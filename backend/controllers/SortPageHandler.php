<?php

abstract class SortPageHandler {
  public function getUrlHandler() 
  {
    $me = $this;
    return function($request, $response, $args) use ($me)
    {
      $requestParams = $request->getQueryParams();
      $sort = isset($requestParams['sort']) ? $requestParams['sort'] : null;
      $results = isset($requestParams['results']) ? $requestParams['results'] : null;
      $page = isset($requestParams['page']) ? $requestParams['page'] : null;
      $me->getItems($request, $response, $sort, $results, $page, $me);
      
    };
  }

  abstract public function getItems($request, $response, $sort, $results, $page, $me);
}