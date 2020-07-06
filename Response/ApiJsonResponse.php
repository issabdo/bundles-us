<?php

namespace Us\Bundle\SecurityBundle\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

class ApiJsonResponse extends JsonResponse
{
    const DEFAULT_CACHE_MAX_AGE = 3600;

    public function getDecodedContent($associativArray = false)
    {
        $content = $this->getContent();
        if (!empty($content)) {
            return json_decode($content, $associativArray);
        }
        return $associativArray===false ? new \stdClass() : [];
    }
//    protected function formatResponseData($data, $status)
//    {
//        $content = [];
//        $content['httpStatus'] = $status;
//        $content['resource'] = $data;
//
//        return $content;
//    }
}