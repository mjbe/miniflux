<?php

require __DIR__.'/common.php';

use JsonRPC\Server;
use Model\Config;

$server = new Server();

$server->authentication(array(
    Config\get('username') => Config\get('api_token')
));

// Get version
$server->register('app.version', function () {
    return array('version' => APP_VERSION);
});

// Get all feeds
$server->register('feed.list', function () {
    $feeds = Model\Feed\get_all();
    if (empty($feeds)) {
        return array();
    }

    $groups = Model\Group\get_feeds_map();
    foreach ($feeds as &$feed) {
        $feed_id = $feed['id'];
        $feed['feed_group_ids'] = array();
        if (isset($groups[$feed_id])) {
            $feed['feed_group_ids'] = $groups[$feed_id];
        }
    }

    return $feeds;
});

// Get one feed
$server->register('feed.info', function ($feed_id) {
    $result = Model\Feed\get($feed_id);
    $result['feed_group_ids'] = Model\Group\get_feed_group_ids($feed_id);
    return $result;
});

// Add a new feed
$server->register('feed.create', function($url) {
    try {
        $result = Model\Feed\create($url);
    } catch (Exception $e) {
        $result = false;
    }

    Model\Config\write_debug();

    return $result;
});

// Delete a feed
$server->register('feed.delete', function($feed_id) {
    return Model\Feed\remove($feed_id);
});

// Delete all feeds
$server->register('feed.delete_all', function() {
    return Model\Feed\remove_all();
});

// Enable a feed
$server->register('feed.enable', function($feed_id) {
    return Model\Feed\enable($feed_id);
});

// Disable a feed
$server->register('feed.disable', function($feed_id) {
    return Model\Feed\disable($feed_id);
});

// Update a feed
$server->register('feed.update', function($feed_id) {
    return Model\Feed\refresh($feed_id);
});

// Get all groups
$server->register('group.list', function () {
    return Model\Group\get_all();
});

//--------------------------------------------------------------------------------
// mjb added 2016.07.24

// Add a new group
$server->register('group.create', function($title) {

    try {
        $result = Model\Group\create($title);
    }
    catch (PicoFeedException $e) {
        $result = false;
    }
    catch (UnexpectedValueException $e) {
        $result = false;
    }

    Model\Config\write_debug();

    return $result;
});

// Get assoc array of group ids with assigned feeds ids

$server->register('group.map', function() {

    try {
        $result = Model\Group\get_map();
    }
    catch (PicoFeedException $e) {
        $result = false;
    }
    catch (UnexpectedValueException $e) {
        $result = false;
    }

    Model\Config\write_debug();

    return $result;
});

// Get the id of a group

$server->register('group.id', function($title) {

    try {
        $result = Model\Group\get_group_id($title);
    }
    catch (PicoFeedException $e) {
        $result = false;
    }
    catch (UnexpectedValueException $e) {
        $result = false;
    }

    Model\Config\write_debug();

    return $result;
});

// Get all feed ids assigned to a group

$server->register('group.feeds', function($group_id) {

    try {
        $result = Model\Group\get_feeds_by_group($group_id);
    }
    catch (PicoFeedException $e) {
        $result = false;
    }
    catch (UnexpectedValueException $e) {
        $result = false;
    }

    Model\Config\write_debug();

    return $result;
});

// add groups to feed

$server->register('group.add', function($feed_id, $group_ids) {

    try {
        $result = Model\Group\add($feed_id, $group_ids);
    }
    catch (PicoFeedException $e) {
        $result = false;
    }
    catch (UnexpectedValueException $e) {
        $result = false;
    }

    Model\Config\write_debug();

    return $result;
});

// remove groups from feed

$server->register('group.remove', function($feed_id, $group_ids) {

    try {
        $result = Model\Group\remove($feed_id, $group_ids);
    }
    catch (PicoFeedException $e) {
        $result = false;
    }
    catch (UnexpectedValueException $e) {
        $result = false;
    }

    Model\Config\write_debug();

    return $result;
});


// remove all groups from feed

$server->register('group.remove_all', function($feed_id) {

    try {
        $result = Model\Group\remove_all($feed_id);
    }
    catch (PicoFeedException $e) {
        $result = false;
    }
    catch (UnexpectedValueException $e) {
        $result = false;
    }

    Model\Config\write_debug();

    return $result;
});


// Purge orphaned groups from database

$server->register('group.purge_groups', function() {

    try {
        $result = Model\Group\purge_groups();
    }
    catch (PicoFeedException $e) {
        $result = false;
    }
    catch (UnexpectedValueException $e) {
        $result = false;
    }

    Model\Config\write_debug();

    return $result;
});


// update feed group associations

$server->register('group.update_feed_groups', function($feed_id, $group_ids, $create_group = '') {

    try {
        $result = Model\Group\update_feed_groups($feed_id, $group_ids, $create_group = '');
    }
    catch (PicoFeedException $e) {
        $result = false;
    }
    catch (UnexpectedValueException $e) {
        $result = false;
    }

    Model\Config\write_debug();

    return $result;
});

//--------------------------------------------------------------------------------


// Get all items for a specific feed
$server->register('item.feed.list', function ($feed_id, $offset = null, $limit = null) {
    return Model\Item\get_all_by_feed($feed_id, $offset, $limit);
});

// Count all feed items
$server->register('item.feed.count', function ($feed_id) {
    return Model\Item\count_by_feed($feed_id);
});

// Get all bookmark items
$server->register('item.bookmark.list', function ($offset = null, $limit = null) {
    return Model\Item\get_bookmarks($offset, $limit);
});

// Count bookmarks
$server->register('item.bookmark.count', function () {
    return Model\Item\count_bookmarks();
});

// Add a bookmark
$server->register('item.bookmark.create', function ($item_id) {
    return Model\Item\set_bookmark_value($item_id, 1);
});

// Remove a bookmark
$server->register('item.bookmark.delete', function ($item_id) {
    return Model\Item\set_bookmark_value($item_id, 0);
});

// Get all unread items
$server->register('item.list_unread', function ($offset = null, $limit = null) {
    return Model\Item\get_all_by_status('unread', array(), $offset, $limit);
});

// Count all unread items
$server->register('item.count_unread', function () {
    return Model\Item\count_by_status('unread');
});

// Get all read items
$server->register('item.list_read', function ($offset = null, $limit = null) {
    return Model\Item\get_all_by_status('read', array(), $offset, $limit);
});

// Count all read items
$server->register('item.count_read', function () {
    return Model\Item\count_by_status('read');
});

// Get one item
$server->register('item.info', function ($item_id) {
    return Model\Item\get($item_id);
});

// Delete an item
$server->register('item.delete', function($item_id) {
    return Model\Item\set_removed($item_id);
});

// Mark item as read
$server->register('item.mark_as_read', function($item_id) {
    return Model\Item\set_read($item_id);
});

// Mark item as unread
$server->register('item.mark_as_unread', function($item_id) {
    return Model\Item\set_unread($item_id);
});

// Change the status of list of items
$server->register('item.set_list_status', function($status, array $items) {
    return Model\Item\set_status($status, $items);
});

// Flush all read items
$server->register('item.flush', function() {
    return Model\Item\mark_all_as_removed();
});

// Mark all unread items as read
$server->register('item.mark_all_as_read', function() {
    return Model\Item\mark_all_as_read();
});

// Get all items with the content
$server->register('item.get_all', function() {
    return Model\Item\get_all();
});

// Get all items since a date
$server->register('item.get_all_since', function($timestamp) {
    return Model\Item\get_all_since($timestamp);
});

// Get all items id and status
$server->register('item.get_all_status', function() {
    return Model\Item\get_all_status();
});

echo $server->execute();
