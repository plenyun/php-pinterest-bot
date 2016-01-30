<?php

namespace seregazhuk\PinterestBot\Helpers\Providers\Traits;

use seregazhuk\PinterestBot\Api\Request;
use seregazhuk\PinterestBot\Helpers\Pagination;
use seregazhuk\PinterestBot\Helpers\UrlHelper;

trait SearchTrait
{
    use ProviderTrait;

    private $moduleSearchPage = "SearchPage";

    /**
     * @return string
     */
    abstract protected function getScope();

    /**
     * Executes search to API. Query - search string.
     *
     * @param string $query
     * @param string $scope
     * @param array  $bookmarks
     * @return array
     */
    public function searchCall($query, $scope, $bookmarks = [])
    {
        $url = UrlHelper::getSearchUrl(! empty($bookmarks));
        $get = $this->createSearchRequest($query, $scope, $bookmarks);
        $url = $url.'?'.UrlHelper::buildRequestString($get);
        $response = $this->getRequest()->exec($url);

        return $this->getResponse()->parseSearchResponse($response, ! empty($bookmarks));
    }

    /**
     * Executes search to API with pagination.
     *
     * @param string $query
     * @param int    $batchesLimit
     * @return \Iterator
     */
    public function searchWithPagination($query, $batchesLimit)
    {
        return Pagination::getPaginatedData(
            [$this, 'searchCall'], [
            'query' => $query,
            'scope' => $this->getScope(),
        ], $batchesLimit
        );
    }

    /**
     * Creates Pinterest API search request
     *
     * @param       $query
     * @param       $scope
     * @param array $bookmarks
     * @return array
     */
    public function createSearchRequest($query, $scope, $bookmarks = [])
    {
        $options = ["scope" => $scope, "query" => $query];
        $dataJson = $this->appendBookMarks($bookmarks, $options);

        return Request::createRequestData(
            $dataJson, "/search/$scope/?q=".$query
        );
    }

    /**
     * Search entities by search query
     *
     * @param string $query
     * @param int    $batchesLimit
     * @return \Iterator
     */
    public function search($query, $batchesLimit = 0)
    {
        return $this->searchWithPagination($query, $batchesLimit);
    }

    /**
     * @param $bookmarks
     * @param $options
     * @return array
     */
    protected function appendBookMarks($bookmarks, $options)
    {
        $dataJson = ['options' => $options];
        if ( ! empty($bookmarks)) {
            $dataJson['options']['bookmarks'] = $bookmarks;

            return $dataJson;
        } else {
            $dataJson = array_merge(
                $dataJson, [
                    'module' => [
                        "name"    => $this->moduleSearchPage,
                        "options" => $options,
                    ],
                ]
            );

            return $dataJson;
        }
    }
}