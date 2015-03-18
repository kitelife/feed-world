/**
 * Created by xiayongfeng on 2015/3/18.
 */
$(function () {

    function getPostsByFeed(feedID) {
        var postListReq = $.ajax({
            type: 'get',
            url: '/feed/' + feedID,
            dataType: 'json'
        });
        postListReq.done(function (resp) {
            if (resp.code === 1000) {
                postListVM.posts = resp.data;
            } else {
                alertify.log(resp.message, 'error', 5000);
            }
        });
    }

    /*
     * 取用户信息
     * */

    var userProfileVM = new Vue({
        el: '#for_user_profile',
        data: {
            user_name: ''
        },
        methods: {
            onClick: function (e) {
                $('#new_feed_modal').modal('show');
            }
        }
    });

    var userProfileReq = $.ajax({
        type: 'get',
        url: '/user/profile',
        dataType: 'json'
    });
    userProfileReq.done(function (resp) {
        if (resp.code === 1000) {
            userProfileVM.user_name = resp.data.name_from;
        } else {
            alertify.log(resp.message, 'error', 5000);
        }
    });

    /*
     * 取订阅列表
     * */

    var feedListVM = new Vue({
        el: '#feed_list',
        data: {
            feeds: []
        },
        methods: {
            listMyPost: function (targetFeed) {
                getPostsByFeed(targetFeed.feed.feed_id);
            },
            unsubscribeIt: function (targetFeed) {
                var unsubscribeReq = $.ajax({
                    type: 'post',
                    url: '/feed/'+ targetFeed.feed.feed_id +'/unsubscribe',
                    dataType: 'json'
                });
                unsubscribeReq.done(function(resp) {
                    if (resp.code === 1000) {
                        alertify.log('成功！', 'success', 1000);
                        setTimeout("window.location.href='/'", 1500);
                    } else {
                        alertify.log(resp.message, 'error', 5000);
                    }
                });
            }
        }
    });

    var feedListReq = $.ajax({
        type: 'get',
        url: '/feed',
        dataType: 'json'
    });
    feedListReq.done(function (resp) {
        if (resp.code === 1000) {
            feedListVM.feeds = resp.data;
            if (feedListVM.feeds.length) {
                getPostsByFeed(feedListVM.feeds[0].feed_id);
            }
        } else {
            alertify.log(resp.message, 'error', 5000);
        }
    });

    /*
     * 取首个订阅的文章列表
     * */

    var postListVM = new Vue({
        el: '#post_list',
        data: {
            posts: []
        }
    });

    var newFeedModal = new Vue({
        el: '#new_feed_modal',
        data: {
            feed_url: ""
        },
        methods: {
            addNewFeed: function (e) {
                var thatViewModel = e.targetVM;
                var req = $.ajax({
                    type: 'post',
                    url: '/feed/subscribe',
                    data: {
                        url: thatViewModel.feed_url
                    },
                    dataType: 'json'
                });
                req.done(function (resp) {
                    if (resp.code === 1000) {
                        $('#new_feed_modal').modal('hide');
                        alertify.log('成功！', 'success', 1000);
                        setTimeout("window.location.href='/'", 1500);
                    } else {
                        alertify.log(resp.message, 'error', 5000);
                    }
                });
            }
        }
    });
});