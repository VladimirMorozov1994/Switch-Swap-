<?php

namespace WeDevs\WeMail\Rest\Help;

use WeDevs\WeMail\Core\Help\Services\PingService;
use WeDevs\WeMail\Rest\Middleware\WeMailMiddleware;
use WeDevs\WeMail\RestController;
use WeDevs\WeMail\Core\Help\SystemInfo;
use WP_REST_Server;

class Help extends RestController {

    public $rest_base = '/help';

    public function register_routes() {
        register_rest_route(
            $this->namespace,
            $this->rest_base . '/system-info',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'permission_callback' => array( $this, 'can_view_wemail' ),
                    'callback'            => array( $this, 'index' ),
                ),
            )
        );

        register_rest_route(
            $this->namespace,
            $this->rest_base . '/tools/ping/send',
            array(
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'permission_callback' => array( $this, 'can_view_wemail' ),
                    'callback'            => array( $this, 'send_ping' ),
                ),
            )
        );

        register_rest_route(
            $this->namespace,
            $this->rest_base . '/tools/ping/receive',
            array(
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'permission_callback' => array( $this, 'can_view_wemail' ),
                    'callback'            => array( $this, 'receive_ping' ),
                ),
            )
        );

        register_rest_route(
            $this->namespace,
            $this->rest_base . '/tools/disconnect',
            array(
                array(
                    'methods'             => WP_REST_Server::DELETABLE,
                    'permission_callback' => array( $this, 'manage_options' ),
                    'callback'            => array( $this, 'disconnect_wemail' ),
                ),
            )
        );
    }

    /**
     * Get all system infos
     * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response
     */
    public function index() {
        $system = new SystemInfo();

        return rest_ensure_response(
            array(
                'data' => $system->allInfo(),
            )
        );
    }

    /**
     * @param $request
     * @return \WP_Error|\WP_REST_Response
     */
    public function send_ping( $request ) {
        $ping = new PingService();

        $callback_url = get_rest_url() . $this->namespace . $this->rest_base . '/tools/ping/receive';

        return rest_ensure_response( $ping->request_send( $request, $callback_url ) );
    }

    /**
     * @param $request
     * @return \WP_Error|\WP_REST_Response
     */
    public function receive_ping( $request ) {
        $ping = new PingService();

        return rest_ensure_response( $ping->request_receive( $request ) );
    }

    /**
     * @return \WP_REST_Response
     */
    public function disconnect_wemail() {
        delete_metadata( 'user', 0, 'wemail_api_key', '', true );
        delete_metadata( 'user', 0, 'wemail_user_data', '', true );

        return new \WP_REST_Response(
            array(
				'status' => 'success',
			)
        );
    }
}
