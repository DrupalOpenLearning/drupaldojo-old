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

class Google_Service_Firestore_IndexOperationMetadata extends Google_Model
{
  public $cancelled;
  protected $documentProgressType = 'Google_Service_Firestore_Progress';
  protected $documentProgressDataType = '';
  public $endTime;
  public $index;
  public $operationType;
  public $startTime;

  public function setCancelled($cancelled)
  {
    $this->cancelled = $cancelled;
  }
  public function getCancelled()
  {
    return $this->cancelled;
  }
  /**
   * @param Google_Service_Firestore_Progress
   */
  public function setDocumentProgress(Google_Service_Firestore_Progress $documentProgress)
  {
    $this->documentProgress = $documentProgress;
  }
  /**
   * @return Google_Service_Firestore_Progress
   */
  public function getDocumentProgress()
  {
    return $this->documentProgress;
  }
  public function setEndTime($endTime)
  {
    $this->endTime = $endTime;
  }
  public function getEndTime()
  {
    return $this->endTime;
  }
  public function setIndex($index)
  {
    $this->index = $index;
  }
  public function getIndex()
  {
    return $this->index;
  }
  public function setOperationType($operationType)
  {
    $this->operationType = $operationType;
  }
  public function getOperationType()
  {
    return $this->operationType;
  }
  public function setStartTime($startTime)
  {
    $this->startTime = $startTime;
  }
  public function getStartTime()
  {
    return $this->startTime;
  }
}
