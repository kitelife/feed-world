<?php
/**
 * Created by PhpStorm.
 * User: xiayongfeng
 * Date: 2015/3/17
 * Time: 16:05
 */

namespace FeedWorld\Helpers;

class CommonUtils
{

    public static function checkLogin($app)
    {
        return isset($_SESSION['user_id']);
    }

    public static function fetchFeed($url)
    {
        $targetURLResponse = \Requests::get($url);
        if (!$targetURLResponse->success) {
            \FeedWorld\Helpers\ResponseUtils::responseError(\FeedWorld\Helpers\CodeStatus::RESOURCE_NOT_ACCESSIBLE);
            return false;
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
            'feed' => $url,
            'post' => array(),
        );
        if ($feedType === 'rss') {
            $thisFeed['title'] = (string)$feedData->channel->title;
            $thisFeed['link'] = (string)$feedData->channel->link;
            $thisFeed['updated_date'] = date("Y-m-d H:i:s", strtotime((string)$feedData->channel->lastBuildDate));

            foreach ($feedData->channel->item as $item) {
                array_push($thisFeed['post'], array(
                    'title' => (string)$item->title,
                    'link' => (string)$item->link,
                    'publish_date' => date("Y-m-d H:i:s", strtotime((string)$item->pubDate)),
                ));
            }
        } else {
            $thisFeed['title'] = (string)$feedData->title;
            $thisFeed['link'] = (string)$feedData->id;
            $thisFeed['updated_date'] = date("Y-m-d H:i:s", strtotime((string)$feedData->updated));

            foreach ($feedData->entry as $entry) {
                $publishedTime = (string)$entry->published;
                if (empty($publishedTime)) {
                    $publishedTime = (string)$entry->updated;
                }

                array_push($thisFeed['post'], array(
                    'title' => (string)$entry->title,
                    'link' => (string)$entry->id,
                    'publish_date' => date('Y-m-d H:i:s', strtotime($publishedTime)),
                ));
            }
        }
        return $thisFeed;
    }
}