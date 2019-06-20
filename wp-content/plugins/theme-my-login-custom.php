<?php

function validate_tml_registration_form_fields( $errors ) {
	if ( empty( $_POST['display_name'] ) ) {
		$errors->add( 'empty_display_name', '<strong>ERROR</strong>: Merci de saisir le nom &agrave; afficher.' );
	}
	return $errors;
}
add_filter( 'registration_errors', 'validate_tml_registration_form_fields' );


function save_tml_registration_form_fields( $user_id ) {
	if ( ! empty( $_POST['display_name'] ) ) {
		//update_user_meta( $user_id, 'display_name', sanitize_text_field( $_POST['display_name'] ) );
		wp_update_user( array( 'ID' => $user_id, 'display_name' => sanitize_text_field( $_POST['display_name'] ) ) );
		wp_update_user( array( 'ID' => $user_id, 'user_login' => $_POST['user_login'] ) );
	}
}
add_action( 'user_register', 'save_tml_registration_form_fields' );


?>
