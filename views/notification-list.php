<ul>
<?php
    foreach($notifications as $notification):
        assert($notification instanceof \WP_Comment);
?>
    <li>
    <?php
        if (!$notification->comment_approved)
            echo "Unread";
        echo $notification->comment_content;
        ?>
    </li>
    <?php
    endforeach;
    ?>
</ul>
