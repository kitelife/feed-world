<?php
/**
 * Created by PhpStorm.
 * User: xiayongfeng
 * Date: 2015/3/16
 * Time: 17:53
 */

namespace FeedWorld\Handlers;

use \FeedWorld\Helpers;

class FeedHandlers
{

    public static function subscribeFeed($app)
    {
        $targetURL = $app->request->post('url', '');
        if ($targetURL === '') {
            Helpers\ResponseUtils::responseError(Helpers\CodeStatus::PARAMETER_NOT_EXISTED);
            return true;
        }

        $targetURL = trim($targetURL);
        if (strpos($targetURL, 'http://') !== 0 && strpos($targetURL, 'https') !== 0) {
            $targetURL = 'http://' . $targetURL;
        }

        $targetURLResponse = \Requests::get($targetURL);
        if (!$targetURLResponse->success) {
            Helpers\ResponseUtils::responseError(Helpers\CodeStatus::RESOURCE_NOT_ACCESSIBLE);
            return true;
        }

        $feedData = new \SimpleXMLElement($targetURLResponse->body, LIBXML_NOWARNING | LIBXML_NOERROR);

        // 如果带channel节点，则应该是rss
        if ($feedData->channel) {
            $feedType = 'rss';
        } else {
            $feedType = 'atom';
        }

        $thisFeed = array(
            'type' => $feedType,
            'feed' => $targetURL,
            'post' => array(),
        );
        if ($feedType === 'rss') {
            $thisFeed['title'] = (string)$feedData->channel->title;
            $thisFeed['link'] = (string)$feedData->channel->link;
            $thisFeed['updated_date'] = (string)$feedData->channel->lastBuildDate;

            foreach ($feedData->channel->item as $item) {
                array_push($thisFeed['post'], array(
                    'title' => (string)$item->title,
                    'link' => (string)$item->link,
                    'publish_date' => (string)$item->pubDate,
                ));
            }
        } else {
            $thisFeed['title'] = (string)$feedData->title;
            $thisFeed['link'] = (string)$feedData->id;
            $thisFeed['updated_date'] = (string)$feedData->updated;

            foreach ($feedData->entry as $entry) {
                array_push($thisFeed['post'], array(
                    'title' => (string)$entry->title,
                    'link' => (string)$entry->id,
                    'publish_date' => (string)$entry->published,
                ));
            }
        }
        // 先看看数据库中是否已经存在
        $checkFeedExist = 'SELECT COUNT(*) FROM feed WHERE site_url = :site_url';
        $stmt = $app->db->prepare($checkFeedExist);
        $stmt->execute(array(':site_url' => $thisFeed['link']));
        if ($stmt->fetchColumn() > 0) {
            Helpers\ResponseUtils::responseError(Helpers\CodeStatus::FEED_EXISTED);
            return true;
        }

        // 存入数据库
        $app->db->beginTransaction();
        try {
            $insertNewFeed = 'INSERT INTO feed (`user_id`, `title`, `site_url`, `feed_url`, `feed_type`, `feed_updated`) '
                . 'VALUES (:user_id, :title, :site_url, :feed_url, :feed_type, :feed_updated)';
            $stmt = $app->db->prepare($insertNewFeed);
            $stmt->execute(array(
                ':user_id' => $_SESSION['user_id'],
                ':title' => $thisFeed['title'],
                ':site_url' => $thisFeed['link'],
                ':feed_url' => $thisFeed['feed'],
                ':feed_type' => $thisFeed['type'],
                ':feed_updated' => $thisFeed['updated_date'],
            ));
            $newFeedID = $app->db->lastInsertId();

            $insertNewPost = 'INSERT INTO post (`feed_id`, `title`, `link`, `publish_date`)'
                . 'VALUES (:feed_id, :title, :link, :publish_date)';
            $stmt = $app->db->prepare($insertNewPost);
            foreach ($thisFeed['post'] as $post) {
                $stmt->execute(array(
                    ':feed_id' => $newFeedID,
                    ':title' => $post['title'],
                    ':link' => $post['link'],
                    ':publish_date' => $post['publish_date'],
                ));
            }
            $app->db->commit();
        } catch (\Exception $e) {
            Helpers\ResponseUtils::responseError(Helpers\CodeStatus::SYSTEM_ERROR);
            $app->db->rollBack();
        }
        Helpers\ResponseUtils::responseJSON($thisFeed);
        return true;
    }

    public static function unsubscribe($app, $id)
    {
        // 先检查一下$id所对应的feed是否属于当前用户
        $checkBelongTo = 'SELECT COUNT(*) FROM feed WHERE feed_id = :feed_id AND user_id := :user_id';
        $stmt = $app->db->prepare($checkBelongTo);
        $stmt->execute(array(
            ':feed_id' => $id,
            ':user_id' => $_SESSION['user_id'],
        ));
        if ($stmt->fetchColumn() === 0) {
            Helpers\ResponseUtils::responseError(Helpers\CodeStatus::WRONG_PARAMETER);
            return true;
        }
        $app->db->beginTransaction();
        try {
            $deletePosts = 'DELETE FROM post WHERE feed_id = :feed_id';
            $stmt = $app->db->prepare($deletePosts);
            $stmt->execute(array(':feed_id' => $id));

            $deleteFeed = 'DELETE FROM feed WHERE feed_id = :feed_id';
            $stmt = $app->prepare($deleteFeed);
            $stmt->execute(array(':feed_id' => $id));

            $app->db->commit();
        } catch (\Exception $e) {
            Helpers\ResponseUtils::responseError(Helpers\CodeStatus::SYSTEM_ERROR);

            $app->db->rollBack();
        }
        return true;
    }

    public static function listFeed($app)
    {
        $selectFeeds = 'SELECT * FROM feed WHERE user_id = :user_id';
        $stmt = $app->db->prepare($selectFeeds);
        $stmt->execute(array(':user_id' => $_SESSION['user_id']));
        $feeds = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Helpers\ResponseUtils::responseJSON($feeds);
        return true;
    }
}