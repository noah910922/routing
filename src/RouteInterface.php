<?php
namespace DevLibs\Routing;

interface RouteInterface
{
    /**
     * @return mixed handler
     */
    public function getHandler();

    /**
     * @param $handler
     */
    public function setHandler($handler);

    /**
     * @return bool determines whether the path is end with slash
     */
    public function getIsEndWithSlash();

    /**
     * @param bool $isEndWithSlash
     */
    public function setIsEndWithSlash($isEndWithSlash);

    /**
     * @return array params
     */
    public function getParams();

    /**
     * @param array $params
     */
    public function setParams($params);

    /**
     * @return array settings
     */
    public function getSettings();

    /**
     * @param array $settings
     */
    public function setSettings($settings);
}