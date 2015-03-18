/**
 * Created by xiayongfeng on 2015/3/18.
 */
$(function () {

    /*
     * 取用户信息
     * */

    var userProfileVM = new Vue({
        el: '#for_user_profile',
        data: {
            user_name: ''
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

    if (feedListVM.feeds.length) {
        var postListReq = $.ajax({
            type: 'get',
            url: '/feed/' + feedListVM.feeds[0].feed_id,
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


    var newFeedButton = new Vue({
        el: '#new_feed_button',
        methods: {
            onClick: function (e) {
                console.log('Test');
                $('#new_feed_modal').modal('show');
            }
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