/* global reframeworkScreenReaderText */
/**
 * Theme functions file.
 *
 * Contains handlers for navigation and widget area.
 */


jQuery(function ($) {
	const darkIcon = '/wp-content/themes/reframework/assets/images/reframe-logo-dark.svg';
	const lightIcon = '/wp-content/themes/reframework/assets/images/reframe-logo.svg';

	$('.menu_logo .mdi').on('click', function changeMenu() {
		const toHide = $('.menu_logo').hasClass('menu-shown');
		if (toHide) {
			hideMenu();
		} else {
			showMenu();
		}
	});

	const $menuActionItem = $('.menu-visible .mdi');
	const $menuIcon = $('#menuIcon');
	const $menuLogo = $('.menu_logo');
	const $menuContainer = $($(".menu").parent());
	const $leftBar = $('.leftbar');
	const $navLinks = $('.nav-links');
	const $carouselContainer = $('.carousel-container');
	function showMenu() {
		$menuContainer.addClass('menu-shown');
		$menuActionItem.removeClass('mdi-menu');
		$menuActionItem.addClass('mdi-arrow-left');
		$menuLogo.addClass('menu-shown');
		$menuIcon.attr('src', darkIcon);
		$leftBar.addClass('leftbar-shadow');
		$navLinks.addClass('hidden');

	}
	function hideMenu() {
		$menuContainer.removeClass('menu-shown');
		$menuActionItem.removeClass('mdi-arrow-left');
		$menuActionItem.addClass('mdi-menu');
		$menuLogo.removeClass('menu-shown');
		$menuIcon.attr('src', lightIcon);
		$leftBar.removeClass('leftbar-shadow');
		$navLinks.removeClass('hidden');
	}

	$carouselContainer.flipster({
		start: 'center',
		loop: true,
		pauseOnHover: true,
		style: 'carousel',
		buttons: true
	});
	$carouselContainer.show();

	const vh = $(window).height();
	const gotoTopMinVH = vh * 1.5;
	const $gotoTopBtn = $('#gotoTop');

	$gotoTopBtn.hide();

	function onScroll($target) {
		if (parseInt($($target).scrollTop()) > parseInt(gotoTopMinVH)) {
			$gotoTopBtn.fadeIn('slow');
		} else {
			$gotoTopBtn.fadeOut('slow');
		}
	}

	$(window).on('scroll', function () {
		onScroll(window);
	});
	
	$('.right-side').on('scroll', function () {
		onScroll('.right-side');
	});

	$($gotoTopBtn).on('click', function () {
		$(".right-side").animate({ scrollTop: 0 }, "slow");
		$('body').animate({ scrollTop: 0 }, "slow");
	});

	$(document).on('click', function (e) {
		const toHide = $('.menu_logo').hasClass('menu-shown');
		if (toHide && !$(e.target).closest(".menu-shown").length) {
			hideMenu();
		}
	});

	$('.clickable').on('click', function (e) {
		const toHide = $('.menu_logo').hasClass('menu-shown');
		if (e.target.tagName === "DIV" || (e.target.tagName === "LI" && !toHide)) {
			e.preventDefault();
			document.location.href = $('.clickable').attr('action');
		}
	});

	$('.carousel-item img').each(function () {
		var maxWidth = 600; // Max width for the image
		var maxHeight = 250;    // Max height for the image
		var ratio = 0;  // Used for aspect ratio
		var width = $(this).width();    // Current image width
		var height = $(this).height();  // Current image height

		// Check if the current width is larger than the max
		if (width > maxWidth) {
			ratio = maxWidth / width;   // get ratio for scaling image
			$(this).css("width", maxWidth); // Set new width
			$(this).css("height", height * ratio);  // Scale height based on ratio
			height = height * ratio;    // Reset height to match scaled image
			width = width * ratio;    // Reset width to match scaled image
		}

		// Check if current height is larger than max
		if (height > maxHeight) {
			ratio = maxHeight / height; // get ratio for scaling image
			$(this).css("height", maxHeight);   // Set new height
			$(this).css("width", width * ratio);    // Scale width based on ratio
			width = width * ratio;    // Reset width to match scaled image
			height = height * ratio;    // Reset height to match scaled image
		}
	});

	$('.content img').each(function () {
		var image = $(this).clone();
		var p = $(this).parent();
		const H = $(this).height();
		const W = $(this).width();
		p.css('position', 'relative');
		image.css('position', 'absolute');
		image.css('top', '0');
		image.css('right', '0');
		image.css('left', '0');
		image.css('bottom', '0');
		$(this).css('filter', 'blur(20px)');
		$(this).css('margin-top', '20px');
		$(this).css('opacity', '0.6');
		$(image).insertAfter(this);
	});
});
