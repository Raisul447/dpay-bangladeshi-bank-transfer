<?php
if (!defined('ABSPATH')) exit;

class WC_Gateway_DPay_Bank extends WC_Payment_Gateway {

    public function __construct() {
        $this->id                 = 'dpay_bank';
        $this->method_title       = 'DPay Bangladeshi Bank Transfer';
        $this->method_description = 'Enable customers to complete manual bank transfers through Bangladeshi payment channels including NPSB, RTGS, BEFTN.';
        $this->has_fields         = true;

        $this->init_form_fields();
        $this->init_settings();

        $this->title        = $this->get_option('title', 'DPay Bangladeshi Bank Transfer');
        $this->description  = $this->get_option('description', 'Pay manually using bank transfer channels such as NPSB, RTGS, or BEFTN. We recommend using the NPSB channel to reduce transaction delays.');
        $this->icon = plugin_dir_url(dirname(__FILE__)) . 'assets/dpay-logo.png';
        $this->bank_name    = $this->get_option('bank_name', 'AB Bank');
        $this->account_no   = $this->get_option('account_no', '123456789');
        $this->branch_name  = $this->get_option('branch_name', 'Dhaka');
        $this->routing_no   = $this->get_option('routing_no', '123456');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        add_action('woocommerce_thankyou_' . $this->id, [$this, 'thank_you_page']);
        add_action('woocommerce_admin_order_data_after_billing_address', [$this, 'admin_order_display'], 10, 1);
    }

    public function get_icon() {
        $icon_url = plugin_dir_url(dirname(__FILE__)) . 'assets/dpay-logo.png';
        return '<img src="' . esc_url($icon_url) . '" alt="DPay" style="margin-left:6px; vertical-align:middle;" />';
    }

    public function init_form_fields() {
        $this->form_fields = [
            'enabled' => [
                'title'   => 'Enable/Disable',
                'type'    => 'checkbox',
                'label'   => 'Enable DPay Bangladeshi Bank Transfer',
                'default' => 'yes',
            ],
            'title' => [
                'title'       => 'Enter your title',
                'type'        => 'text',
                'description' => 'This title will displayed on the checkout page (leave unchanged if you wish to keep it the same).',
                'default'     => 'DPay Bangladeshi Bank Transfer',
            ],
            'description' => [
                'title'       => 'Payment Description',
                'type'        => 'textarea',
                'description' => 'This description will displayed on the checkout page (leave unchanged if you wish to keep it the same).',
                'default'     => 'Pay manually using bank transfer channels such as NPSB, RTGS, or BEFTN. We recommend using the NPSB channel to reduce transaction delays.',
            ],
            'bank_name' => [
                'title'       => 'Bank Name',
                'type'        => 'text',
                'default'     => 'AB Bank',
            ],
            'account_no' => [
                'title'       => 'Bank A/C No.',
                'type'        => 'text',
                'default'     => '123456789',
            ],
            'branch_name' => [
                'title'       => 'Branch Name',
                'type'        => 'text',
                'default'     => 'Dhaka',
            ],
            'routing_no' => [
                'title'       => 'Routing Number',
                'type'        => 'text',
                'default'     => '123456',
            ],
        ];
    }

    public function payment_fields() {
        echo wpautop(wp_kses_post($this->description));
        echo '<ul>';
        echo '<li><strong>Bank Name:</strong> ' . esc_html($this->bank_name) . '</li>';
        echo '<li><strong>Bank A/C No.:</strong> ' . esc_html($this->account_no) . '</li>';
        echo '<li><strong>Branch Name:</strong> ' . esc_html($this->branch_name) . '</li>';
        echo '<li><strong>Routing Number:</strong> ' . esc_html($this->routing_no) . '</li>';
        echo '</ul>';
        echo '<p><label for="dpay_transaction_id"><strong>Enter your transaction number:</strong></label></p>';
        echo '<input type="text" class="input-text" name="dpay_transaction_id" id="dpay_transaction_id" required />';
    }

    public function validate_fields() {
        if (empty($_POST['dpay_transaction_id'])) {
            wc_add_notice('Please enter your bank transaction number before placing the order.', 'error');
            return false;
        }
        return true;
    }

    public function process_payment($order_id) {
        $order = wc_get_order($order_id);

        if (!empty($_POST['dpay_transaction_id'])) {
            $order->update_meta_data('dpay_transaction_id', sanitize_text_field($_POST['dpay_transaction_id']));
        }

        $order->update_status('on-hold', 'Awaiting dpay bangladeshi manual bank transfer');
        $order->reduce_order_stock();
        WC()->cart->empty_cart();

        return [
            'result'   => 'success',
            'redirect' => $this->get_return_url($order),
        ];
    }

    public function admin_order_display($order) {
        $transaction_id = $order->get_meta('dpay_transaction_id');
        if ($transaction_id) {
            echo '<p><strong>Transaction Number:</strong> ' . esc_html($transaction_id) . '</p>';
        }
    }

    public function thank_you_page() {
        echo '<p>Thank you for placing your order. It is currently on hold until we confirm the payment.</p>';
    }
}
