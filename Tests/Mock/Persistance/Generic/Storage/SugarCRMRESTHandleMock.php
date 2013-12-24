<?php
namespace EssentialDots\EdSugarcrm\Tests\Mock\Persistance\Generic\Storage;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Nikola Stojiljkovic, Essential Dots d.o.o. Belgrade
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

class SugarCRMRESTHandleMock {

    const NUMBER_OF_ELEMENTS = 3;

    /**
     * @var \array
     */
    private static $lastRequest = array();

    /**
     * @var \TYPO3\CMS\Extbase\Reflection\ReflectionService
     * @inject
     */
    protected $reflectionService;

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @inject
     */
    protected $configurationManager;

    /**
     * @param array $apiParameters
     * @param string $method
     * @return mixed
     */
    public function execQuery(array $apiParameters, $method = 'get_entry_list') {
        $function = $this->getParameterName($method);
        $result = call_user_func(array($this, $function), $apiParameters);
        return $result;
    }

	/**
	 *
	 */
	public function setRESTUrl(){}

	/**
	 *
	 */
	public function setUsername(){}

	/**
	 *
	 */
    public function setPassword(){}

	/**
	 *
	 */
    public function connect(){
        return true;
    }

	/**
	 * @param array $apiParameters
	 * @return array
	 */
	protected function setEntry($apiParameters){
        self::$lastRequest[$apiParameters['module_name']] = $apiParameters['name_value_list'];
        $result = array(
            "id" => md5(1),
            'entry_list' => $apiParameters['name_value_list']
        );
        return $result;
    }

	/**
	 * @param array $apiParameters
	 * @return array
	 */
	protected function getEntryList($apiParameters){
        $className = $this->getClassName($apiParameters["module_name"]);
        $class = $this->reflectionService->getClassSchema($className);
        $properties = $class->getProperties();
        $this->extractQuery($apiParameters["query"], $parameters);
        $propertiesResult = array();
        $enrtyList = array();
        $frameworkConfiguration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $mapping = $frameworkConfiguration['persistence']['classes'][$className]['mapping']['columns'];
        $list = $apiParameters["max_results"];
        $newAdded = NULL;
        if (isset(self::$lastRequest[$apiParameters['module_name']])){
            $newAdded = self::$lastRequest[$apiParameters['module_name']];
            self::$lastRequest[$apiParameters['module_name']] = NULL;
        }
        if ($list == 0){
            $list = self::NUMBER_OF_ELEMENTS;
        }
        for($i = 1; $i < $list + 1; $i++){
            $propertiesResult = array();
            foreach($apiParameters['select_fields'] as $property){
                $array = array(
                    'name' => $property,
                    'value' => $property
                );
                if (isset($parameters[$property])){
                    $array['value'] = $parameters[$property];
                }elseif($property == "id" || $property == "uid"){
                    $array['value'] = md5($property.$i);
                }else{
                    $map = $property;
                    if (isset($mapping[$property]['mapOnProperty'])){
                        $map = $mapping[$property]['mapOnProperty'];
                    }
                    if (!isset($properties[$map])){
                        $map = $this->getParameterName($map);
                    }
                    if (isset($properties[$map])){
                        switch($properties[$map]['type']){
                            case 'string':
                                $array['value'] = $map.$i;
                                break;
                            case 'integer':
                                $array['value'] = hexdec(md5($map.$i));
                                break;
                            case 'DateTime':
                            case 'datetime':
                                $array['value'] = NULL;
                                break;
                        }
                    }
                }
                if ($newAdded){
                    foreach($newAdded as $value){
                        if ($value['name'] == $map || $value['name'] == $property){
                            $array['value'] = $value['value'];
                            break;
                        }
                    }
                }
                $propertiesResult[$property] = $array;
            }
            $propertiesResult = array(
                "id" => md5($property.$i),
                "module_name" => $apiParameters["module_name"],
                'name_value_list' => $propertiesResult
            );
            array_push($enrtyList, $propertiesResult);
        }
        $result = array(
            "result_count" => $list,
            "total_count" => $list,
            "next_offset" => $list,
            'entry_list' => $enrtyList,
            "relationship_list" => 0
        );
        return $result;
    }

	/**
	 * @param array $apiParameters
	 * @return array
	 */
	protected function getEntries($apiParameters){
        $className = $this->getClassName($apiParameters["module_name"]);
        $class = $this->reflectionService->getClassSchema($className);
        $properties = $class->getProperties();
        $this->extractQuery($apiParameters["query"], $parameters);
        $propertiesResult = array();
        $enrtyList = array();
        $frameworkConfiguration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $mapping = $frameworkConfiguration['persistence']['classes'][$className]['mapping']['columns'];
        $i = 1;
        $newAdded = NULL;
        if (isset($apiParameters['module_name'])){
            $newAdded = $apiParameters['module_name'];
            $apiParameters['module_name'] = NULL;
        }
        foreach($apiParameters["ids"] as $id){
            $propertiesResult = array();
            foreach($apiParameters['select_fields'] as $property){
                $array = array(
                    'name' => $property,
                    'value' => $property
                );
                if (isset($parameters[$property])){
                    $array['value'] = $parameters[$property];
                }elseif($property == "id" || $property == "uid"){
                    $array['value'] = $id;
                }else{
                    $map = $property;
                    if (isset($mapping[$property]['mapOnProperty'])){
                        $map = $mapping[$property]['mapOnProperty'];
                    }
                    if (!isset($properties[$map])){
                        $map = $this->getParameterName($map);
                    }
                    if (isset($properties[$map])){
                        switch($properties[$map]['type']){
                            case 'string':
                                $array['value'] = $map.$i;
                                break;
                            case 'integer':
                                $array['value'] = hexdec(md5($map.$id));
                                break;
                            case 'DateTime':
                            case 'datetime':
                                $array['value'] = NULL;
                                break;
                        }
                    }
                }
                if ($newAdded){
                    foreach($newAdded as $value){
                        if ($value['name'] == $map){
                            $array['value'] = $value['value'];
                            break;
                        }
                    }
                }
                $propertiesResult[$property] = $array;
            }
            $propertiesResult = array(
                "id" => $id,
                "module_name" => $apiParameters["module_name"],
                'name_value_list' => $propertiesResult
            );
            array_push($enrtyList, $propertiesResult);
            $i++;
        }
        $result = array(
            'entry_list' => $enrtyList,
            "relationship_list" => 0
        );
        return $result;
    }

	/**
	 * @return array
	 */
	protected function getEntriesCount(){
        $result = array(
            'result_count' => self::NUMBER_OF_ELEMENTS
        );
        return $result;
    }

	/**
	 * @param $query
	 * @param $parameters
	 */
	protected function extractQuery($query, &$parameters){
        $parameter = NULL;
        $value = NULL;
        if (strpos($query, "(")){
            $query = trim(trim($query, '('), ')');
            $queries = explode('AND', $query);
        }else{
            $queries = array($query);
        }
        $parameters = array();
        foreach($queries as $query){
            $query = explode("=", $query);
            if (count($query) == 2){
                $helper = explode(".", trim($query[0]));
                $parameter = $helper[1];
                $value = trim(trim($query[1]), "'");
                $parameters[$parameter] = $value;
            }
        }
    }

	/**
	 * @param $initial
	 * @return string
	 */
	protected function getParameterName($initial){
        $helper = explode("_", $initial);
        $name = "";
        for ($i = 0; $i < count($helper); $i++){
            if (!$i){
                $name .= $helper[$i];
            }else{
                $name .= strtoupper(substr($helper[$i],0,1)) . substr($helper[$i],1);
            }
        }
        return $name;
    }

	/**
	 * @param $module_name
	 * @return string
	 */
	protected function getClassName($module_name){
        $className = substr($module_name,0,-1);
        if ($className == "Case"){
            $className = "SupportCase";
        }
        $className = 'EssentialDots\\EdSugarcrm\\Domain\\Model\\'. $className;
        return $className;
    }
} 