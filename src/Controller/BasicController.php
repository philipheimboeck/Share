<?php
/**
 * User: Philip Heimböck
 * Date: 23.10.14
 * Time: 16:26
 */

namespace Controller;
use Service\Service;

/**
 * Class BasicController
 *
 * @Path("/")
 */
class BasicController {

    /**
     * @var Service
     *
     * @Service("Service\Service")
     */
    protected $service;

    /**
     * @param mixed $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }

    /**
     * @return string
     *
     * @Method("Get")
     * @Path("/")
     * @Template("templates/Service/index.php")
     */
    public function getData() {
        return array("msg" => "GET hello world");
    }

    /**
     * @Method("Post")
     * @Path()
     * @Template("templates/Service/JsonTemplate.php")
     * @param $data1
     * @param $data2
     * @return array
     */
    public function sendData($data1, $data2="default") {
        return array("msg" => "POST hello world", $data1, $data2);
    }

    /**
     * @return string
     *
     * @Method("Get")
     * @Path("/service")
     * @Template("templates/Service/index.php")
     */
    public function getServiceData() {
        return array("msg" => $this->service->provideServiceData());
    }
} 