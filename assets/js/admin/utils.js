/**
 * Utils
 */
import jQuery from 'jquery'; // eslint-disable-line import/no-unresolved
/*
 * Return delay speed of Typewriter
 *
 * @param int strLen String length
 * @returns {number}
 */
export const getTypewriterSpeed = (strLen) => {
	if (strLen > 500) {
		return 0;
	}

	if (strLen > 200) {
		return 5;
	}

	if (strLen > 100) {
		return 20;
	}

	return 30;
};

export const isTinyMCEActive = () => {
	if (jQuery('#wp-content-wrap').hasClass('tmce-active')) {
		return true;
	}

	return false;
};
