<?php
    $containerClass = "message-center";
    if (empty($notifications))
        $containerClass .= " message-center-empty";
?>
<div class="<?php echo $containerClass ?>">
<?php
    foreach($notifications as $notification):
        assert($notification instanceof \Yibby\Models\Notification);
?>

        <a href="javascript:void(0)" class="
                            message-item
                            d-flex
                            align-items-center
                            border-bottom
                            px-3
                            py-2
                          ">
            <div class="read-status <?php echo $notification->read ? 'read' : 'unread' ?>">

            </div>
                  <span class="btn btn-light-purple btn-circle">
                    <i class="bi <?php echo $notification->icon ?>"></i>
                  </span>
            <div class="notification-content d-inline-block v-middle ps-3">
                <h5 class="message-title mb-0 mt-1 fw-bold">
                    <?php echo $notification->subject ?>
                </h5>
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
        </a>
    <?php
    endforeach;
    ?>
</div>
