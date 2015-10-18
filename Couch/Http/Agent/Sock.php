<?php
namespace Couch\Http\Agent;

use \Couch\Http\Request;

class Sock
    extends \Couch\Http\Agent
{
    public function run(Request $request) {
        $url = parse_url($request->uri);
        if (isset($url['query'])) {
            $url['query'] = '?'. $url['query'];
        } else {
            $url['query'] = '';
        }

        $this->link =@ fsockopen(
            $url['host'],
            $url['port'],
            $this->failCode,
            $this->failText,
            $this->config['timeout']
        );

        if ($this->failText !== '') {
            throw new \Exception($this->failText, $this->failCode);
        }

        if (is_resource($this->link)) {
            $headers = [];
            $headers['Host'] = $url['host'];
            $headers['Connection'] = 'close';
            $headers = $headers + $request->headers;

            $theRequest = sprintf("%s %s%s HTTP/1.1\r\n",
                $request->method, $url['path'], $url['query']);
            foreach ($headers as $key => $val) {
                $theRequest .= sprintf("%s:%s\r\n", $key, $val);
            }
            $theRequest .= "\r\n";

            $body = $request->body;
            if (!empty($body) && is_array($body)) {
                $body = json_encode($body);
            } else {
                $body = '';
            }
            $theRequest .= $body;

            $request->setBodyRaw($theRequest);

            fwrite($this->link, $theRequest);

            stream_set_timeout($this->link, $this->config['timeout']);
            stream_set_blocking($this->link, $this->config['blocking']);
            $meta = stream_get_meta_data($this->link);

            $theResponse = '';
            while (!feof($this->link)) {
                if ($meta['timed_out']) {
                    throw new \Exception('Time out!');
                }
                $theResponse .= fgets($this->link, 1024);
                $meta = stream_get_meta_data($this->link);
            }

            fclose($this->link);
            $this->link = null;

            return $theResponse;
        }

        return false;
    }
}
