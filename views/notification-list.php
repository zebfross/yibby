<?php
    $containerClass = "message-center";
    if (empty($notifications))
        $containerClass .= " message-center-empty";
?>
<div class="<?php echo $containerClass ?>">
<?php
    foreach($notifications as $notification):
        assert($notification instanceof \Yibby\Models\Notification);
    $classes = "message-item
                            d-flex
                            align-items-center
                            border-bottom
                            px-3
                            py-2";
    $tag = "div";
if (!empty($notification->link)) {
    $tag = "a";
}
?>
        <<?php echo $tag ?> href="<?php echo $notification->link ?>" class="<?php echo $classes ?>">
            <div class="read-status <?php echo $notification->read ? 'read' : 'unread' ?>">

            </div>
                  <span class="btn <?php echo $notification->color ?> btn-circle">
                    <i class="bi <?php echo $notification->icon ?>"></i>
                  </span>
            <div class="notification-content d-inline-block v-middle ps-3">
                <span class="
                                d-block
                                fw-normal
                                mt-1
                              "><?php echo $notification->body ?></span>
                <span class="
                                text-nowrap
                                d-block
                                subtext
                                text-muted
                              "><?php echo $notification->friendly_date() ?> ago</span>
            </div>
        </<?php echo $tag ?>>
    <?php
    endforeach;
    ?>
</div>
