/**
 * Documentation page functionality
 * - Intersection Observer for TOC highlighting
 */

document.addEventListener( 'DOMContentLoaded', () => {
	initTocHighlighting();
} );

/**
 * Initialize the Table of Contents highlighting functionality.
 * Uses Intersection Observer to detect when headers are in the viewport.
 * Keeps a section active until the next section becomes active.
 */
function initTocHighlighting(): void {
	const tocLinks = document.querySelectorAll( '.toc-list a' );
	const headers: Element[] = [];

	// Skip if no TOC links are found
	if ( ! tocLinks.length ) {
		return;
	}

	// Collect all headers that have matching TOC links
	tocLinks.forEach( ( link ) => {
		const targetId = link.getAttribute( 'href' )?.replace( '#', '' );
		if ( targetId ) {
			const targetHeader = document.getElementById( targetId );
			if ( targetHeader ) {
				headers.push( targetHeader );
			}
		}
	} );

	// Skip if no headers with IDs are found
	if ( ! headers.length ) {
		return;
	}

	// Create map to find TOC link by header id
	const headerToLinkMap = new Map< string, Element >();
	tocLinks.forEach( ( link ) => {
		const targetId = link.getAttribute( 'href' )?.replace( '#', '' );
		if ( targetId ) {
			headerToLinkMap.set( targetId, link );
		}
	} );

	// Helper to clear active class from all TOC links
	const clearActiveLinks = () => {
		tocLinks.forEach( ( link ) => {
			link.classList.remove( 'toc-active' );
		} );
	};

	// Sort headers by their position in the document (top to bottom)
	const sortedHeaders = [...headers].sort((a, b) => {
		const aPos = a.getBoundingClientRect().top + window.scrollY;
		const bPos = b.getBoundingClientRect().top + window.scrollY;
		return aPos - bPos;
	});

	// Create intersection observer with margin that primarily detects when headers enter the viewport
	const observerOptions: IntersectionObserverInit = {
		rootMargin: '-10% 0px -85% 0px', // Detect headers near the top of the viewport
		threshold: [0, 0.1], // Lower thresholds to detect earlier
	};

	// Track the current active section
	let currentActiveSectionId: string | null = null;

	/**
	 * Find the closest header above the viewport's top edge
	 * This helps us determine which section is currently active
	 */
	function findCurrentSection(): string | null {
		const scrollY = window.scrollY;
		const scrollThreshold = scrollY + window.innerHeight * 0.15; // 15% from the top

		// Handle case when scrolled to the top of the page
		if (scrollY <= 10) {
			if (sortedHeaders.length > 0) {
				return sortedHeaders[0].id;
			}
			return null;
		}

		// Find the last header that is above the threshold
		for (let i = sortedHeaders.length - 1; i >= 0; i--) {
			const headerPos = sortedHeaders[i].getBoundingClientRect().top + scrollY;
			if (headerPos <= scrollThreshold) {
				return sortedHeaders[i].id;
			}
		}

		// If no header is found, return the first one as default
		return sortedHeaders.length > 0 ? sortedHeaders[0].id : null;
	}

	/**
	 * Update the active TOC link based on the current section
	 */
	function updateActiveTocLink() {
		const currentSectionId = findCurrentSection();

		if (currentSectionId && currentSectionId !== currentActiveSectionId) {
			// Clear existing active state
			clearActiveLinks();

			// Set the new active link
			const link = headerToLinkMap.get(currentSectionId);
			if (link) {
				link.classList.add('toc-active');
				currentActiveSectionId = currentSectionId;

				// Scroll the TOC to keep active link visible if needed
				const tocContainer = document.querySelector('.doc-right');
				if (tocContainer) {
					const linkRect = link.getBoundingClientRect();
					const containerRect = tocContainer.getBoundingClientRect();

					// Check if link is outside visible area of container
					if (
						linkRect.bottom > containerRect.bottom ||
						linkRect.top < containerRect.top
					) {
						link.scrollIntoView({
							behavior: 'smooth',
							block: 'center',
						});
					}
				}
			}
		}
	}

	// The observer is now primarily used to trigger updates
	// when headings enter or leave the viewport
	const observer = new IntersectionObserver((entries) => {
		// If any header enters or leaves viewport, recalculate active section
		if (entries.some(entry => entry.isIntersecting)) {
			updateActiveTocLink();
		}
	}, observerOptions);

	// Observe all headers
	headers.forEach((header) => {
		observer.observe(header);
	});

	// Also update on scroll for smooth transitions between sections
	let scrollTimeout: number | null = null;
	window.addEventListener('scroll', () => {
		if (scrollTimeout) {
			window.clearTimeout(scrollTimeout);
		}

		// Use timeout to throttle scroll events
		scrollTimeout = window.setTimeout(() => {
			updateActiveTocLink();
		}, 100);
	});

	// Initial update when page loads
	updateActiveTocLink();

	// Handle TOC link clicks to smoothly scroll to the target header
	tocLinks.forEach((link) => {
		link.addEventListener('click', (e) => {
			e.preventDefault();
			const targetId = link.getAttribute('href')?.replace('#', '');
			if (targetId) {
				const targetHeader = document.getElementById(targetId);
				if (targetHeader) {
					// Scroll to header
					targetHeader.scrollIntoView({ behavior: 'smooth' });
					// Update URL hash
					history.pushState(null, '', `#${targetId}`);
					// Also handle active state immediately
					clearActiveLinks();
					link.classList.add('toc-active');
					currentActiveSectionId = targetId;
				}
			}
		});
	});
}
