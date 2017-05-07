<?php

/**
 * Description of Good
 *
 */
class Good extends ORM
{
  protected static $has_many = ['Good_Review'];
}
