<?php

namespace Yibby;

use BracketSpace\Notification\Abstracts;
use BracketSpace\Notification\Defaults\Field;

/**
 * ExampleRecipient Recipient
 */
class PushRecipient extends Abstracts\Recipient {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct( [
            'slug'          => 'recipient_slug',
            'name'          => __( 'Yibby Push Recipient', 'textdomain' ),
            'default_value' => 'default',
        ] );
    }

    /**
     * Parses raw recipient value to something consumable by the Carrier.
     *
     * @param  string $value Raw value saved by the user.
     * @return array         Array of resolved values
     */
    public function parse_value( $value = '' ) {

        if ( empty( $value ) ) {
            $value = [ $this->get_default_value() ];
        }



        // Keep in mind you should return an array here.
        // This is because you may select a recipient which parses to multiple values.
        // Example: User Role recipient may parse to multiple emails.
        return [ $value ];

    }

    /**
     * Prints the Recipient field.
     *
     * @return Field
     */
    public function input() {

        // You should build an array of options here if you are using SelectField field.
        $opts = [
            'author' => __( 'Post Author', 'textdomain' ),
            'admin' => __( 'Administrator', 'textdomain' ),
        ];

        // You can use other fields as well.
        return new Field\SelectField( [
            'label'     => __( 'Yibby Push Recipient', 'textdomain' ),
            'name'      => 'recipient',       // Don't change this.
            'css_class' => 'recipient-value', // Don't change this.
            'value'     => $this->get_default_value(),
            'pretty'    => true,
            'options'   => $opts,
        ] );

    }

}