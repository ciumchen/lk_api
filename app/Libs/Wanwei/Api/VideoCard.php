<?php

namespace Wanwei\Api;

class VideoCard extends RequestBase
{
    
    public function getList()
    {
        $apiMethod = '1715-1';
        $ShowApi = $this->getShowApi($apiMethod);
        $response = $ShowApi->post();
        return $response->getContent();
    }
}
