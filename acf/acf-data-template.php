<?php if (have_rows('project_content_desktop')) : ?>
	<?php while (have_rows('project_content_desktop')) : the_row(); ?>
		<?php if (get_row_layout() == 'full_width_image') : ?>
			<?php $image = get_sub_field('image'); ?>
			<?php if ($image) : ?>
				<img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" />
			<?php endif; ?>
		<?php elseif (get_row_layout() == 'video') : ?>
			<?= get_sub_field('vimeo_url'); ?>
		<?php elseif (get_row_layout() == 'content_image_video') : ?>
			<?php $background_image = get_sub_field('background_image'); ?>
			<?php if ($background_image) : ?>
				<img src="<?php echo esc_url($background_image['url']); ?>" alt="<?php echo esc_attr($background_image['alt']); ?>" />
			<?php endif; ?>
			<?= get_sub_field('title'); ?>
			<?php $logo = get_sub_field('logo'); ?>
			<?php if ($logo) : ?>
				<img src="<?php echo esc_url($logo['url']); ?>" alt="<?php echo esc_attr($logo['alt']); ?>" />
			<?php endif; ?>
			<?= get_sub_field('subtitle'); ?>
			<?= get_sub_field('content__copy'); ?>
			<?= get_sub_field('details'); ?>
			<?php $client_logo = get_sub_field('client_logo'); ?>
			<?php if ($client_logo) : ?>
				<img src="<?php echo esc_url($client_logo['url']); ?>" alt="<?php echo esc_attr($client_logo['alt']); ?>" />
			<?php endif; ?>
		<?php endif; ?>
	<?php endwhile; ?>
<?php else : ?>
	<?php // no layouts found 
	?>
<?php endif; ?>
<?php if (have_rows('project_content_mobile')) : ?>
	<?php while (have_rows('project_content_mobile')) : the_row(); ?>
		<?php if (get_row_layout() == 'intro_content') : ?>
			<?= get_sub_field('title'); ?>
			<?php $logo = get_sub_field('logo'); ?>
			<?php if ($logo) : ?>
				<img src="<?php echo esc_url($logo['url']); ?>" alt="<?php echo esc_attr($logo['alt']); ?>" />
			<?php endif; ?>
			<?= get_sub_field('subtitle'); ?>
			<?= get_sub_field('content__copy'); ?>
			<?= get_sub_field('details'); ?>
			<?php $client_logo = get_sub_field('client_logo'); ?>
			<?php if ($client_logo) : ?>
				<img src="<?php echo esc_url($client_logo['url']); ?>" alt="<?php echo esc_attr($client_logo['alt']); ?>" />
			<?php endif; ?>
		<?php elseif (get_row_layout() == 'full_width_image') : ?>
			<?php $image = get_sub_field('image'); ?>
			<?php if ($image) : ?>
				<img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" />
			<?php endif; ?>
		<?php elseif (get_row_layout() == 'video') : ?>
			<?= get_sub_field('vimeo_url'); ?>
		<?php endif; ?>
	<?php endwhile; ?>
<?php else : ?>
	<?php // no layouts found 
	?>
<?php endif; ?>
<?php $thumbnail = get_field('thumbnail'); ?>
<?php if ($thumbnail) : ?>
	<img src="<?php echo esc_url($thumbnail['url']); ?>" alt="<?php echo esc_attr($thumbnail['alt']); ?>" />
<?php endif; ?>