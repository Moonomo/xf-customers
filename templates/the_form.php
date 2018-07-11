<?php

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;

/**
 * @var Xfrontend\Customers\FormFields $fields
 */
$fields      = $data->fields;
$fields_data = $fields->get_fields_data();
$name        = $fields->get_field( 'fullname' );
$phone       = $fields->get_field( 'phone' );
$email       = $fields->get_field( 'email' );
$budget      = $fields->get_field( 'budget' );
$message     = $fields->get_field( 'message' );

?>

<div id="<?php echo esc_attr( $data->id ); ?>" class="xf-row <?php echo esc_attr( $data->class ); ?>">
	<form method="post">
		<input type="hidden" name="<?php echo esc_attr( $fields::FIELD_SCOPE ); ?>" value="add">
		<input type="hidden" name="<?php echo esc_attr( $fields::FIELD_DATE ); ?>"
				value="<?php echo esc_html( $fields->get_date_via_rest_api() ); ?>">

		<?php
		// @codingStandardsIgnoreStart Everything is escaped.
		echo $fields->nonce_field();
		?>

		<?php
		// Show success/error notice.
		do_action( \XFrontend\Customers\Notice::ACTION_PRINT );
		?>

		<div class="xf-form-group">
			<label for="<?php echo esc_attr( $name->id ); ?>" class="xf-control-label">
				<?php echo esc_html( $name->label ); ?>
			</label>
			<input
					name="<?php echo esc_attr( $name->name ); ?>"
					type="<?php echo esc_attr( $name->type ); ?>"
					class="xf-form-control"
					id="<?php echo esc_attr( $name->id ); ?>"
					maxlength="<?php echo esc_attr( $name->maxlength ); ?>"
					value="<?php echo esc_attr( $fields_data[ $name->name ] ); ?>"
			>
		</div>

		<div class="xf-form-group">
			<label for="<?php echo esc_attr( $phone->id ); ?>" class="xf-control-label">
				<?php echo esc_html( $phone->label ); ?>
			</label>
			<input
					name="<?php echo esc_attr( $phone->name ); ?>"
					type="<?php echo esc_attr( $phone->type ); ?>"
					class="xf-form-control"
					id="<?php echo esc_attr( $phone->id ); ?>"
					maxlength="<?php echo esc_attr( $phone->maxlength ); ?>"
					value="<?php echo esc_attr( $fields_data[ $phone->name ] ); ?>">
		</div>

		<div class="xf-form-group">
			<label for="<?php echo esc_attr( $email->id ); ?>" class="xf-control-label">
				<?php echo esc_html( $email->label ); ?>
			</label>
			<input
					name="<?php echo esc_attr( $email->name ); ?>"
					type="<?php echo esc_attr( $email->type ); ?>"
					class="xf-form-control"
					id="<?php echo esc_attr( $email->id ); ?>"
					maxlength="<?php echo esc_attr( $email->maxlength ); ?>"
					value="<?php echo esc_attr( $fields_data[ $email->name ] ); ?>">
		</div>

		<div class="xf-form-group">
			<label for="<?php echo esc_attr( $budget->id ); ?>" class="xf-control-label">
				<?php echo esc_html( $budget->label ); ?>
			</label>
			<input
					name="<?php echo esc_attr( $budget->name ); ?>"
					type="<?php echo esc_attr( $budget->type ); ?>"
					class="xf-form-control"
					id="<?php echo esc_attr( $budget->id ); ?>"
					maxlength="<?php echo esc_attr( $budget->maxlength ); ?>"
					value="<?php echo esc_attr( $fields_data[ $budget->name ] ); ?>">
		</div>

		<div class="xf-form-group">
			<label for="<?php echo esc_attr( $message->id ); ?>" class="xf-control-label">
				<?php echo esc_html( $message->label ); ?>
			</label>
			<textarea
					name="<?php echo esc_attr( $message->name ); ?>"
					class="xf-form-control"
					id="<?php echo esc_attr( $message->id ); ?>"
					maxlength="<?php echo esc_attr( $message->maxlength ); ?>"
					cols="<?php echo esc_attr( $message->cols ); ?>"
					rows="<?php echo esc_attr( $message->rows ); ?>"
			><?php echo esc_textarea( $fields_data[ $message->name ] ); ?></textarea>
		</div>

		<div class="xf-form-group">
			<button type="submit"
					class="xf-btn xf-btn-primary xf__button"><?php esc_html_e( 'Submit', 'xf-customers' ); ?></button>
		</div>

		<div class="ajax-response"></div>
	</form>
</div>
