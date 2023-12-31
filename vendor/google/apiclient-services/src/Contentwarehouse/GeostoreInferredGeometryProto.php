<?php
/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

namespace Google\Service\Contentwarehouse;

class GeostoreInferredGeometryProto extends \Google\Collection
{
<<<<<<< HEAD
  protected $collection_key = 'definesGeometryFor';
  protected $definesGeometryForType = GeostoreFeatureIdProto::class;
  protected $definesGeometryForDataType = 'array';
  public $definesGeometryFor;
  protected $geometryCompositionType = GeostoreGeometryComposition::class;
  protected $geometryCompositionDataType = '';
  public $geometryComposition;
=======
  protected $collection_key = 'includesGeometryOf';
  protected $definesGeometryForType = GeostoreFeatureIdProto::class;
  protected $definesGeometryForDataType = 'array';
  public $definesGeometryFor;
  protected $excludesGeometryOfType = GeostoreFeatureIdProto::class;
  protected $excludesGeometryOfDataType = 'array';
  public $excludesGeometryOf;
  protected $includesGeometryOfType = GeostoreFeatureIdProto::class;
  protected $includesGeometryOfDataType = 'array';
  public $includesGeometryOf;
>>>>>>> 1f8fa8284 (env)

  /**
   * @param GeostoreFeatureIdProto[]
   */
  public function setDefinesGeometryFor($definesGeometryFor)
  {
    $this->definesGeometryFor = $definesGeometryFor;
  }
  /**
   * @return GeostoreFeatureIdProto[]
   */
  public function getDefinesGeometryFor()
  {
    return $this->definesGeometryFor;
  }
  /**
<<<<<<< HEAD
   * @param GeostoreGeometryComposition
   */
  public function setGeometryComposition(GeostoreGeometryComposition $geometryComposition)
  {
    $this->geometryComposition = $geometryComposition;
  }
  /**
   * @return GeostoreGeometryComposition
   */
  public function getGeometryComposition()
  {
    return $this->geometryComposition;
=======
   * @param GeostoreFeatureIdProto[]
   */
  public function setExcludesGeometryOf($excludesGeometryOf)
  {
    $this->excludesGeometryOf = $excludesGeometryOf;
  }
  /**
   * @return GeostoreFeatureIdProto[]
   */
  public function getExcludesGeometryOf()
  {
    return $this->excludesGeometryOf;
  }
  /**
   * @param GeostoreFeatureIdProto[]
   */
  public function setIncludesGeometryOf($includesGeometryOf)
  {
    $this->includesGeometryOf = $includesGeometryOf;
  }
  /**
   * @return GeostoreFeatureIdProto[]
   */
  public function getIncludesGeometryOf()
  {
    return $this->includesGeometryOf;
>>>>>>> 1f8fa8284 (env)
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GeostoreInferredGeometryProto::class, 'Google_Service_Contentwarehouse_GeostoreInferredGeometryProto');
