// --- Video Player Functions ---

/**
 * Loads video content into the dashboard.
 * In a real application, this would fetch specific video details based on a lesson ID
 * or user's progress.
 */
async function loadVideoContent() {
    const videoPlayer = document.getElementById('video-player');
    const lessonTitle = document.getElementById('current-lesson-title');
    const lessonDescription = document.getElementById('current-lesson-description');

    if (!videoPlayer || !lessonTitle || !lessonDescription) {
        console.warn("Video player elements not found. This script might be on the wrong page.");
        return;
    }

    // Get lesson ID from URL query parameter (e.g., dashboard.html?lesson=algebra)
    const urlParams = new URLSearchParams(window.location.search);
    const requestedLessonId = urlParams.get('lesson') || 'default'; // 'default' if no lesson specified

    try {
        const response = await fetch('backend/api/videos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('jwt_token')}` // Send token
            },
            body: JSON.stringify({ action: 'get_video', lesson_id: requestedLessonId })
        });

        const result = await response.json();

        if (result.success && result.video) {
            const videoData = result.video;
            lessonTitle.textContent = `Current Lesson: ${videoData.title}`;
            lessonDescription.textContent = videoData.description;
            videoPlayer.src = videoData.url;
            videoPlayer.poster = videoData.thumbnail || 'https://placehold.co/1280x720/60a5fa/ffffff?text=Video+Thumbnail';
            videoPlayer.load(); // Load the new video source
        } else {
            lessonTitle.textContent = 'Lesson Not Found';
            lessonDescription.textContent = 'Could not load the requested lesson. ' + (result.message || '');
            videoPlayer.src = ''; // Clear video source
            showMessageBox('Error loading video: ' + (result.message || 'Unknown error.'));
        }
    } catch (error) {
        console.error('Error fetching video content:', error);
        lessonTitle.textContent = 'Error Loading Lesson';
        lessonDescription.textContent = 'An error occurred while fetching video details.';
        showMessageBox('An error occurred while fetching video content. Please try again later.');
    }
}
