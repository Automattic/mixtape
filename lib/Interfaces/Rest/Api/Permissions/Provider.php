<?php

interface Mixtape_Interfaces_Rest_Api_Permissions_Provider {
    /**
     * @param WP_REST_Request $request
     * @return bool
     */
    public function permissions_check( $request, $action );
}