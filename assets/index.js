/**
 * Created by xiayongfeng on 2015/3/18.
 */
$(function () {

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
                    $('#new_feed_modal').modal('hide');
                    console.log(resp);
                });
            }
        }
    })
});