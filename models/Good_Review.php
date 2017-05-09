<?php

/**
 * Description of Good_Review
 *
 */
class Good_Review extends ORM
{
  protected static $belongs_to = ['User', 'Good'];
  
}
