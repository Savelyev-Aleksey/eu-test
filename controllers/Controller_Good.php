<?php

/**
 * Description of Controller_Good
 *
 */
class Controller_Good extends Controller_Base_Auth
{

  public function action_index()
  {
    $user = User::get_authorized_user();
    $goods = Good::all('name.ASC');

    self::view('index', ['goods' => $goods, 'user' => $user]);
  }



  public function action_show()
  {
    $id = Router::param('id');
    try
    {
      $good = Good::find($id);
    }
    catch (Exception $ex)
    {
      Request::redirect('public/404.php');
    }
    self::view('show', ['good' => $good]);
  }



  public function action_review()
  {
    if (!Request::is_post())
    {
      Request::redirect('/');
    }

    $user_id = User::get_authorized_user()->id;

    $values = Request::filter('post', ['id', 'good_id', 'rate', 'comment']);

    if (isset($values['id']))
    {
      try
      {
        $review = Good_Review::find($values['id']);
      }
      catch (Exception $ex)
      {

      }
    }
  }

}
