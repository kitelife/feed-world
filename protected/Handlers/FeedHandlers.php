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

    protected static function insertPosts($posts, $feedID, $app)
    {
        $insertNewPost = 'INSERT INTO post (`feed_id`, `title`, `link`, `publish_date`)'
            . 'VALUES (:feed_id, :title, :link, :publish_date)';
        $stmt = $app->db->prepare($insertNewPost);
        foreach ($posts as $onePost) {
            $stmt->execute(array(
                ':feed_id' => $feedID,
                ':title' => $onePost['title'],
                ':link' => $onePost['link'],
                ':publish_date' => $onePost['publish_date'],
            ));
        }
    }

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
        $thisFeed = Helpers\CommonUtils::fetchFeed($targetURL, $app->settings);
        if ($thisFeed === false) {
            return true;
        }
        // 先看看数据库中是否已经存在
        $checkFeedExist = 'SELECT COUNT(*) FROM feed WHERE site_url = :site_url AND user_id=:user_id';
        $stmt = $app->db->prepare($checkFeedExist);
        $stmt->execute(array(':site_url' => $thisFeed['link'], ':user_id' => $_SESSION['user_id']));
        if (intval($stmt->fetchColumn()) > 0) {
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
            self::insertPosts($thisFeed['post'], $newFeedID, $app);
            $app->db->commit();
        } catch (\Exception $e) {
            Helpers\ResponseUtils::responseError(Helpers\CodeStatus::SYSTEM_ERROR);
            $app->db->rollBack();
        }
        Helpers\ResponseUtils::responseJSON($thisFeed);
        return true;
    }

    public static function unsubscribe($app)
    {
        $feedID = $app->request->post('feed_id', null);
        if ($feedID === null) {
            Helpers\ResponseUtils::responseError(Helpers\CodeStatus::PARAMETER_NOT_EXISTED);
            return true;
        }
        // 先检查一下$id所对应的feed是否属于当前用户
        $checkBelongTo = 'SELECT COUNT(*) FROM feed WHERE feed_id = :feed_id AND user_id = :user_id';
        $stmt = $app->db->prepare($checkBelongTo);
        $stmt->execute(array(
            ':feed_id' => $feedID,
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
            $stmt->execute(array(':feed_id' => $feedID));

            $deleteFeed = 'DELETE FROM feed WHERE feed_id = :feed_id';
            $stmt = $app->db->prepare($deleteFeed);
            $stmt->execute(array(':feed_id' => $feedID));

            $app->db->commit();
        } catch (\Exception $e) {
            Helpers\ResponseUtils::responseError(Helpers\CodeStatus::SYSTEM_ERROR);
            $app->db->rollBack();
        }
        Helpers\ResponseUtils::responseJSON('成功取消订阅！');
        return true;
    }

    public static function listFeed($app)
    {
        $selectFeeds = 'SELECT * FROM feed WHERE user_id = :user_id ORDER BY feed_id';
        $stmt = $app->db->prepare($selectFeeds);
        $stmt->execute(array(':user_id' => $_SESSION['user_id']));
        $feeds = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $countUnread = 'SELECT COUNT(*) FROM post WHERE feed_id = :feed_id AND is_read = 0';
        $stmt = $app->db->prepare($countUnread);
        foreach ($feeds as $index => $feed) {
            $stmt->execute(array(':feed_id' => $feed['feed_id']));
            $feeds[$index]['unread_count'] = $stmt->fetchColumn();
        }

        Helpers\ResponseUtils::responseJSON($feeds);
        return true;
    }

    public static function updateFeed($app, $feedID)
    {
        $selectFeedURL = 'SELECT feed_url, feed_updated FROM feed WHERE user_id=:user_id AND feed_id=:feed_id';
        $stmt = $app->db->prepare($selectFeedURL);
        $stmt->execute(array(
            ':user_id' => $_SESSION['user_id'],
            ':feed_id' => $feedID,
        ));
        $oneRow = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (empty($oneRow)) {
            Helpers\ResponseUtils::responseError(Helpers\CodeStatus::PARAMETER_NOT_EXISTED);
            return true;
        }
        $newFeedData = Helpers\CommonUtils::fetchFeed($oneRow['feed_url'], $app->settings);
        if ($newFeedData === false) {
            Helpers\ResponseUtils::responseError(Helpers\CodeStatus::RESOURCE_NOT_ACCESSIBLE);
            return true;
        }

        $lastFeedUpdated = date("Y-m-d H:i:s", strtotime($oneRow['feed_updated']));

        if ($newFeedData['updated_date'] > $lastFeedUpdated) {
            // 选出原数据中最新一篇post的发布时间
            $selectLatestPostTime = 'SELECT title, publish_date FROM post WHERE feed_id=:feed_id ORDER BY publish_date DESC, post_id DESC LIMIT 1';
            $stmt = $app->db->prepare($selectLatestPostTime);
            $stmt->execute(array(':feed_id' => $feedID));
            $oneRow = $stmt->fetch(\PDO::FETCH_ASSOC);

            $hasPost = empty($oneRow) ? false : true;
            $lastPostTime = $hasPost ? date("Y-m-d H:i:s", strtotime($oneRow['publish_date'])) : null;

            $newPosts = array();
            foreach ($newFeedData['post'] as $onePost) {
                // 这里假设feed中的post是按发布时间从迟到早先后排序的
                // 如果发布时间和标题都一样，那么之后的就不用比较了（都是之前就已经存储在数据库中了）
                if ($hasPost) {
                    $publishDate = date("Y-m-d H:i:s", strtotime($onePost['publish_date']));
                    if ($publishDate === $lastPostTime && strcmp($oneRow['title'], $onePost['title']) === 0) {
                        break;
                    } else if ($publishDate > $lastPostTime
                        || ($publishDate === $lastPostTime && strcmp($oneRow['title'], $onePost['title']))
                    ) {
                        $newPosts[] = $onePost;
                    }
                } else {
                    $newPosts[] = $onePost;
                }
            }
            self::insertPosts($newPosts, $feedID, $app);
        }

        $dataToReturn = array();
        $countUnread = 'SELECT COUNT(*) FROM post WHERE feed_id = :feed_id AND is_read = 0';
        $stmt = $app->db->prepare($countUnread);
        $stmt->execute(array(':feed_id' => $feedID));
        $dataToReturn['unread_count'] = intval($stmt->fetchColumn());

        Helpers\ResponseUtils::responseJSON($dataToReturn);
        return true;
    }
}