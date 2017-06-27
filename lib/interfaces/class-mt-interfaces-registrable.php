<?php

interface MT_Interfaces_Registrable {
	/**
	 * Register This thing with an environment
	 *
	 * @param MT_Environment $environment The Environment to use.
	 * @return void
	 */
	function register( $environment );
}