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

namespace Google\Service\Integrations\Resource;

<<<<<<< HEAD
=======
use Google\Service\Integrations\GoogleCloudIntegrationsV1alphaListTaskEntitiesResponse;

>>>>>>> 1f8fa8284 (env)
/**
 * The "locations" collection of methods.
 * Typical usage is:
 *  <code>
 *   $integrationsService = new Google\Service\Integrations(...);
 *   $locations = $integrationsService->projects_locations;
 *  </code>
 */
class ProjectsLocations extends \Google\Service\Resource
{
<<<<<<< HEAD
=======
  /**
   * This is a UI only method and will be moved away. Returns a list of common
   * tasks. (locations.listTaskEntities)
   *
   * @param string $parent Required. The location resource of the request. This is
   * not going to be used but preserve the field for future.
   * @param array $optParams Optional parameters.
   * @return GoogleCloudIntegrationsV1alphaListTaskEntitiesResponse
   */
  public function listTaskEntities($parent, $optParams = [])
  {
    $params = ['parent' => $parent];
    $params = array_merge($params, $optParams);
    return $this->call('listTaskEntities', [$params], GoogleCloudIntegrationsV1alphaListTaskEntitiesResponse::class);
  }
>>>>>>> 1f8fa8284 (env)
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ProjectsLocations::class, 'Google_Service_Integrations_Resource_ProjectsLocations');