<?php
namespace DevLibs\Routing;

class Route implements RouteInterface
{
    protected $isEndWithSlash;

    protected $handler;

    protected $params = [];

    protected $settings = [];

    public function getHandler()
    {
        return $this->handler;
    }

    public function setHandler($handler)
    {
        $this->handler = $handler;
    }

    public function getIsEndWithSlash()
    {
        return $this->isEndWithSlash;
    }

    public function setIsEndWithSlash($isEndWithSlash)
    {
        $this->isEndWithSlash = $isEndWithSlash;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function setParams($params)
    {
        $this->params = $params;
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function setSettings($settings)
    {
        $this->settings = $settings;
    }
}