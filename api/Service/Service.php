<?php
/**
 * User: Philip Heimböck
 * Date: 16.10.14
 * Time: 19:02
 */

/**
 * Class Service
 *
 * @Path("/")
 */
class Service {

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
} 