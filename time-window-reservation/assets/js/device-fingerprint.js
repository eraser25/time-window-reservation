function getDeviceFingerprint() {
	const fingerprints = [
		navigator.userAgent,
		navigator.language,
		screen.width + 'x' + screen.height,
		new Date().getTimezoneOffset(),
		navigator.hardwareConcurrency || 'unknown',
		navigator.deviceMemory || 'unknown',
	];

	const combined = fingerprints.join('|');

	// Simple hash function
	let hash = 0;
	for (let i = 0; i < combined.length; i++) {
		const char = combined.charCodeAt(i);
		hash = ((hash << 5) - hash) + char;
		hash = hash & hash; // Convert to 32bit integer
	}

	return Math.abs(hash).toString(16);
}