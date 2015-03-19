<?php
/**
 * Created by PhpStorm.
 * User: xiayongfeng
 * Date: 2015/3/17
 * Time: 13:16
 */

namespace FeedWorld\Handlers;

use \FeedWorld\Helpers;

class PostHandlers
{

    public static function listPost($app, $feedID)
    {
        $selectPosts = 'SELECT post.* FROM post, feed WHERE post.feed_id = :feed_id AND post.feed_id = feed.feed_id AND feed.user_id = :user_id ORDER BY post.publish_date DESC';
        $stmt = $app->db->prepare($selectPosts);
        $stmt->execute(array(
                ':feed_id' => $feedID,
                ':user_id' => $_SESSION['user_id']
            )
        );
        $posts = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        Helpers\ResponseUtils::responseJSON($posts);
        return true;
    }

    public static function changePostStatus($app, $feedID, $postID)
    {
        $setRead = $app->request->post('set_read', null);
        $setStar = $app->request->post('set_star', null);

        if ($setRead === null && $setStar === null) {
            Helpers\ResponseUtils::responseError(Helpers\CodeStatus::PARAMETER_NOT_EXISTED);
            return true;
        }

        if ($setRead !== null) {
            $setRead = intval($setRead);
            $setReadStatus = 'UPDATE post SET is_read = :is_read WHERE feed_id = :feed_id AND post_id = :post_id '
                . 'AND (SELECT COUNT(*) FROM feed WHERE feed_id = :feed_id AND user_id = :user_id) > 0';
            $stmt = $app->db->prepare($setReadStatus);
            $stmt->execute(array(
                ':is_read' => $setRead,
                ':feed_id' => $feedID,
                ':post_id' => $postID,
                ':user_id' => $_SESSION['user_id'],
            ));
        }

        if ($setStar !== null) {
            $setStar = intval($setStar);
            $setStarStatus = 'UPDATE post SET is_star = :is_star WHERE feed_id = :feed_id AND post_id = :post_id '
                . 'AND (SELECT COUNT(*) FROM feed WHERE feed_id = :feed_id AND user_id = :user_id) > 0';
            $stmt = $app->db->prepare($setStarStatus);
            $stmt->execute(array(
                ':is_star' => $setStar,
                ':feed_id' => $feedID,
                ':post_id' => $postID,
                ':user_id' => $_SESSION['user_id'],
            ));
        }
        Helpers\ResponseUtils::responseJSON('修改成功！');
        return true;
    }
}