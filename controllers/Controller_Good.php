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
      // UPDATE OR DELETE STATE
      try
      {
        $review = Good_Review::find($values['id']);
      }
      catch (Exception $ex)
      {
        Session::flash('Review update is incorrect. Wrong parameters');
        Request::redirect('/');
      }

      if ($review->user_id != $user_id)
      {
        Session::flash('Sorry it\'s is not you review. Try later.');
        Request::redirect('/');
      }

      // if not set rate then DELETE review
      if ($values['rate'] === '')
      {
        $res = $review->remove();
        if ($res)
        {
          Session::flash('Review was deleted');
          Request::redirect('/');
        }
        else
        {
          Session::flash('Can\'t remove review: ' . $review->get_error());
          Request::redirect('/');
        }
      }
      // UPDATE state
      unset($values['good_id']); // exclude to change existing good_id
      $review->values($values);
    }
    else
    {
      if ($values['rate'] === '')
      {
        Session::flash('Rating not set. Nothing to save.');
        Request::redirect('/');
      }
      // INSERT STATE with non empty rate
      $values['user_id'] = $user_id;
      $review = new Good_Review($values);
    }

    if ($review->save())
    {
      Session::flash('Review saved.');
    }
    else
    {
      Session::flash('<strong>Error:</strong> ' . $review->get_error());
    }
    Request::redirect('/');
  }

}
