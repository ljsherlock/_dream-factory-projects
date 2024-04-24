window.addEventListener( 'load', () => {
	const isMobile = function() {
		return ! window.matchMedia( '(min-width: 64em)' ).matches;
	};

	// Setup Swiper
	// new Swiper( '.swiper', {
	// 	navigation: {
	// 		nextEl: ".dfp-button-next",
	// 		prevEl: ".dfp-button-prev",
	// 	}
	// } );

	let galleries = document.querySelectorAll( '.df-gallery' );

	galleries.forEach( gallery => {
		let hasLoaded = false;

		let items = gallery.querySelectorAll( '.item' );
		let itemsContainer = gallery.querySelector( '.items' );
		var contentElements = [];
	
		let activeItem;
		let activeContent;
		let activeVideo;

		const maybeStartVideo = function( swiperContainer ) {
			if ( swiperContainer.swiper === undefined || swiperContainer.swiper.slides.length === 0 ) {
				return;
			}

			let activeEl = swiperContainer.swiper.slides[ swiperContainer.swiper.activeIndex ];


			if ( activeEl.dataset.layout == 'video' ) {
				if ( ! activeEl.video ) {
					let iframe = activeEl.querySelector( 'iframe' );
					if ( iframe ) {
						activeEl.video = new Vimeo.Player( iframe );
					}
				}
				activeEl.video.setVolume(0.5);
				activeEl.video.play();
				activeVideo = activeEl.video;
			}
		};

		const maybeStopVideo = function() {
			if ( activeVideo ) {
				activeVideo.pause();
			}
		};
	
		// Calculate offsets
		const calculateOffsets = function() {
			if ( items.length == 0 ) {
				return;
			}

			if(items.length !== contentElements.length) {
				contentElements = document.querySelectorAll( '.df-gallery .item-content' );
			}

			let galleryRect = gallery.getBoundingClientRect();
			let boundingRect = items[0].getBoundingClientRect();
			let maxElements = Math.floor( galleryRect.width / boundingRect.width );
			
			let row = 0;
			items.forEach( ( item, index ) => {
				if ( index % maxElements == 0 ) {
					row++;
				}

				// 16:9 items
				// item.originalOffset = boundingRect.width * 0.5625 * row; // height * row ... height derived from width at 16:9.
				// item.scrollToOffset = item.originalOffset + galleryRect.top + window.scrollY - ( boundingRect.width * 0.5625 * 0.5 );

				// 1:1 items
				item.originalOffset = boundingRect.width * row; // height * row ... height derived from width at 1:1.

				if(contentElements[ index] !== undefined) {
					let contentElement = contentElements[ index ];
					let contentHeight = contentElement.getBoundingClientRect().width * 0.5625;
				
					item.scrollToOffset = item.originalOffset + galleryRect.top + window.scrollY - ( ( window.innerHeight - contentHeight ) / 2 );
				}
			} );
		}

		const getContentElement = function( item ) {
			return document.querySelector( '.item-content[data-item-id="' + activeItem.dataset.itemId + '"]' );
		}

		const closeContent = function() {
			if ( ! activeItem ) {
				return;
			}

			let contentElement = getContentElement();
			if ( contentElement ) {
				contentElement.classList.remove( 'active', 'fade-in' );
				contentElement.classList.add( 'is-hidden' );

				maybeStopVideo();
			}

			activeItem.classList.remove( 'active' );
			activeItem = null;
			itemsContainer.classList.remove( 'has-active' );
		}

		const openContent = function( item ) {
			if ( activeItem ) {
				closeContent();
			}

			if ( ! hasLoaded ) {
				// calculateOffset(item);
				hasLoaded = true;
			}

			itemsContainer.classList.add( 'has-active' );
			item.classList.add( 'active' );
			activeItem = item;

			let contentElement = getContentElement( item );
			if ( ! contentElement ) {
				console.error( 'CONTENT NOT FOUND' );
				return;
			}
			activeContent = contentElement;
			
			contentElement.classList.add( 'fade-in', 'active' );
			contentElement.classList.remove( 'is-hidden' );

			// here is where we can load the video
			renderVideos(contentElement);

			contentElement.querySelectorAll( '.swiper' ).forEach( el => {
				let isVisible = function( e ) {
					return !!( e.offsetWidth || e.offsetHeight || e.getClientRects().length );
				}
				if ( isVisible( el ) ) {
					maybeStopVideo();
					maybeStartVideo( el );
				}
			} );

			window.scrollTo( {
				top: item.scrollToOffset,
				behavior: 'smooth'
			} );
		
		}
		/**
		 * 
		 * https://regexr.com/7sjjo
		 * 
		 * @param {*} url 
		 * @returns Array
		 */
		const getIDs = (url) => {
			
			const regex = /^(?:http|https)?:\/\/?vimeo\.com\/(?:(\d+)\/?(\w+|\/?))/;
			const found = url.match(regex);

			return found;
		}

		const generateVimeoPlayerURL = (vimeoID) => {
			let vimeoPlayerURL =  'https://player.vimeo.com/video/' + vimeoID[1];

			if(vimeoID[2] !== undefined) {
				vimeoPlayerURL += '?h=' + vimeoID[2];
				vimeoPlayerURL += '&app_id=122963';
			} else {
				vimeoPlayerURL += '?app_id=122963';
			}
			
			return vimeoPlayerURL;
		}

		const renderVideos = (contentElement) => {
			const videoWrappers = contentElement.querySelectorAll('.swiper-slide.video .video-wrapper');

			videoWrappers.forEach(video => {
				if ( ! video.hasChildNodes() ) {
					renderVideo(video);
				}
			} );
		}

		const renderVideo = (video) => {

			const vimeoURL = video.getAttribute('data-tdf-vimeo-url');
			const vimeoIDs = getIDs(vimeoURL); 
			
			// console.log(vimeoIDs);

			const vimeoPlayerURL = generateVimeoPlayerURL(vimeoIDs);
		
			let iframe = document.createElement('iframe');
			
			iframe.setAttribute('src', vimeoPlayerURL);
			iframe.setAttribute('frameborder', '0');
			iframe.setAttribute('allow', 'autoplay; fullscreen; picture-in-picture');
			iframe.setAttribute('data-ready', 'true');
			
			video.appendChild(iframe);
		}

		const getHiddenContent = async (_data) => {
			const response = await fetch( tdf_hidden_project_content.ajax_url, {
				method: 'POST',
				body: ( new URLSearchParams(_data) ).toString(),
				headers: { 'Content-type': 'application/x-www-form-urlencoded' }
			});

			return response;
		}

		// console.log('items', items);

		const processHiddenContent = (items, i) => {

			// Load hidden content asynchronously.
			const projectID = items[i].getAttribute('data-item-id');
			const item = items[i];

			// send ID via ajax to our php script 
			// which will return HTML
			let _data = { action : 'tdf_hidden_project_content', id: projectID };

			getHiddenContent(_data)
			.then(response => response.text())
			.then(project_html => {
				// add HTML below this item.
				item.insertAdjacentHTML('afterend', project_html);
				item.classList.add('item--content-loaded');
				
				item.addEventListener( 'click', ( e ) => {
					e.preventDefault();
					if ( item.classList.contains( 'active' ) ) {
						return;
					}
					openContent( item );
				} );

				// Slider Change Listeners
				let swipers = document.querySelectorAll( ".item-content[data-item-id='"+projectID+"'] .swiper");

				swipers.forEach( element => {
					new Swiper( element, {
						navigation: {
							nextEl: element.querySelector(".dfp-button-next"),
							prevEl: element.querySelector(".dfp-button-prev")
						},
						keyboard: {
							enabled: true,
							onlyInViewport: false,
						},
					} );
					element.swiper.on( 'slideChange', ( swiper ) => {
						maybeStopVideo();
						maybeStartVideo( element );
					} );
				} );

				// Close action.
				let closeButton = document.querySelector( ".item-content[data-item-id='"+projectID+"'] .close-button" );
				closeButton.addEventListener( 'click', ( e ) => {
					e.preventDefault();
					closeContent();
				});

				i++;

				if (i < items.length) {
					processHiddenContent(items, i);
				}
			});
		}

		processHiddenContent(items, 0);
		// console.log('started');

		// var itemsProcessed = 0;
		// console.log(itemsProcessed);
	
		// Click listeners
		// items.forEach( ( item, index, array ) => {

			// // Load hidden content asynchronously.
			// const projectID = item.getAttribute('data-item-id');

			// // send ID via ajax to our php script 
			// // which will return HTML
			// let _data = { action : 'tdf_hidden_project_content', id: projectID };

			// getHiddenContent(_data)
			// .then(response => response.text())
			// .then(project_html => {
			// 	// add HTML below this item.
			// 	item.insertAdjacentHTML('afterend', project_html);

			// 	item.classList.add('item--content-loaded');

			// 	item.addEventListener( 'click', ( e ) => {
			// 		e.preventDefault();
			// 		if ( item.classList.contains( 'active' ) ) {
			// 			return;
			// 		}
	
			// 		openContent( item );
			// 	} );

			// 	// Slider Change Listeners
			// 	let swipers = document.querySelectorAll( ".item-content[data-item-id='"+projectID+"'] .swiper");

			// 	swipers.forEach(element => {
			// 		new Swiper( element, {
			// 			navigation: {
			// 				nextEl: element.querySelector(".dfp-button-next"),
			// 				prevEl: element.querySelector(".dfp-button-prev")
			// 			}
			// 		} );
			// 		element.swiper.on( 'slideChange', ( swiper ) => {
			// 			maybeStopVideo();
			// 			maybeStartVideo( element );
			// 		} );
			// 	});

			// 	// Close action.
			// 	let closeButton = document.querySelector( ".item-content[data-item-id='"+projectID+"'] .close-button" );
			// 	closeButton.addEventListener( 'click', ( e ) => {
			// 		e.preventDefault();
			// 		closeContent();
			// 	});
			// });
		
			// itemsProcessed++;

			// console.log('processed ' + projectID);

			// if(itemsProcessed === array.length) {
			// 	callback();
			// }
		// });

		window.addEventListener( 'resize', () => {
			if ( activeItem ) {
				calculateOffsets();
			}
		} );
	});
});




