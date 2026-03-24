# Real-Time Content Updates Implementation Guide

This guide explains how to implement real-time content updates on your frontend website so that changes made by admins are immediately reflected without requiring a page refresh.

## Backend Implementation (Already Done)

The backend is configured to:
1. **Broadcast Events**: When content is created, updated, or deleted, a `ContentUpdated` event is broadcast to a public channel
2. **Update Timestamp**: A global `content_updated_at` timestamp is stored in cache and updated on every change
3. **Public Channel**: All updates are broadcast to the `content-updates` channel (no authentication required)

## Frontend Implementation Options

You have two options for real-time updates:

### Option 1: Polling (Simpler - Recommended)

Poll the API endpoint to check for content updates. This is simpler and works without WebSockets.

#### JavaScript Example:

```javascript
// Configuration
const API_BASE_URL = 'http://localhost:8000/api/v1';
const POLL_INTERVAL = 2000; // Check every 2 seconds

let lastUpdateTime = null;

// Function to check for updates
async function checkForUpdates() {
    try {
        const response = await fetch(`${API_BASE_URL}/content-last-updated`);
        const data = await response.json();
        
        if (data.success) {
            const currentUpdateTime = data.data.last_updated_at;
            
            // If timestamp changed, refresh content
            if (lastUpdateTime && lastUpdateTime !== currentUpdateTime) {
                console.log('Content updated! Refreshing...');
                refreshWebsiteContent();
            }
            
            lastUpdateTime = currentUpdateTime;
        }
    } catch (error) {
        console.error('Error checking for updates:', error);
    }
}

// Function to refresh website content
function refreshWebsiteContent() {
    // Reload all content sections
    loadHomePageContent();
    loadAboutSectionContent();
    loadServiceSectionContent();
    loadOurFactsContent();
    // ... load other sections
}

// Start polling when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Get initial timestamp
    checkForUpdates();
    
    // Poll every 2 seconds
    setInterval(checkForUpdates, POLL_INTERVAL);
});
```

### Option 2: WebSockets with Laravel Echo (Advanced)

Use Laravel Echo to listen for real-time events. This requires additional setup.

#### Step 1: Install Laravel Echo and Pusher JS

```bash
npm install --save laravel-echo pusher-js
```

#### Step 2: Configure Echo

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    forceTLS: true
});

// Listen for content updates
window.Echo.channel('content-updates')
    .listen('.content.updated', (e) => {
        console.log('Content updated:', e);
        
        // Refresh specific content based on content_type
        switch(e.content_type) {
            case 'home-page':
                loadHomePageContent();
                break;
            case 'about-section':
                loadAboutSectionContent();
                break;
            case 'service-section':
                loadServiceSectionContent();
                break;
            case 'our-facts-section':
            case 'our-fact':
                loadOurFactsContent();
                break;
            case 'our-promise':
                loadOurPromiseContent();
                break;
            case 'process-step':
                loadProcessStepsContent();
                break;
            default:
                // Refresh all content
                refreshWebsiteContent();
        }
    });
```

## React Example (Polling)

```jsx
import { useEffect, useState } from 'react';

function useContentUpdates(apiBaseUrl = 'http://localhost:8000/api/v1', interval = 2000) {
    const [lastUpdateTime, setLastUpdateTime] = useState(null);
    const [updateCount, setUpdateCount] = useState(0);

    useEffect(() => {
        let isMounted = true;

        const checkForUpdates = async () => {
            try {
                const response = await fetch(`${apiBaseUrl}/content-last-updated`);
                const data = await response.json();
                
                if (data.success && isMounted) {
                    const currentUpdateTime = data.data.last_updated_at;
                    
                    if (lastUpdateTime && lastUpdateTime !== currentUpdateTime) {
                        console.log('Content updated!');
                        setUpdateCount(prev => prev + 1);
                        // Trigger content refresh
                        window.dispatchEvent(new CustomEvent('contentUpdated'));
                    }
                    
                    setLastUpdateTime(currentUpdateTime);
                }
            } catch (error) {
                console.error('Error checking for updates:', error);
            }
        };

        // Initial check
        checkForUpdates();

        // Set up polling
        const intervalId = setInterval(checkForUpdates, interval);

        return () => {
            isMounted = false;
            clearInterval(intervalId);
        };
    }, [lastUpdateTime, apiBaseUrl, interval]);

    return { updateCount };
}

// Usage in component
function App() {
    useContentUpdates();

    useEffect(() => {
        const handleContentUpdate = () => {
            // Refresh all content
            refreshAllContent();
        };

        window.addEventListener('contentUpdated', handleContentUpdate);
        
        return () => {
            window.removeEventListener('contentUpdated', handleContentUpdate);
        };
    }, []);

    return (
        // Your app content
    );
}
```

## Vue.js Example (Polling)

```vue
<template>
  <div>
    <!-- Your content -->
  </div>
</template>

<script>
export default {
  data() {
    return {
      lastUpdateTime: null,
      pollInterval: null
    };
  },
  mounted() {
    this.checkForUpdates();
    this.pollInterval = setInterval(this.checkForUpdates, 2000);
  },
  beforeUnmount() {
    if (this.pollInterval) {
      clearInterval(this.pollInterval);
    }
  },
  methods: {
    async checkForUpdates() {
      try {
        const response = await fetch('http://localhost:8000/api/v1/content-last-updated');
        const data = await response.json();
        
        if (data.success) {
          const currentUpdateTime = data.data.last_updated_at;
          
          if (this.lastUpdateTime && this.lastUpdateTime !== currentUpdateTime) {
            console.log('Content updated! Refreshing...');
            this.refreshContent();
          }
          
          this.lastUpdateTime = currentUpdateTime;
        }
      } catch (error) {
        console.error('Error checking for updates:', error);
      }
    },
    refreshContent() {
      // Refresh all content sections
      this.$store.dispatch('content/refreshAll');
      // Or call individual refresh methods
    }
  }
};
</script>
```

## API Endpoint

### Get Last Content Update Time

**Endpoint**: `GET /api/v1/content-last-updated`

**Response**:
```json
{
    "success": true,
    "data": {
        "last_updated_at": "2026-01-13T10:30:45.000000Z",
        "timestamp": 1705145445
    }
}
```

## Broadcast Events

When content is updated, the backend broadcasts events with this structure:

```json
{
    "content_type": "our-facts-section",
    "action": "updated",
    "data": {
        "id": 1
    },
    "timestamp": "2026-01-13T10:30:45.000000Z"
}
```

**Content Types**:
- `home-page`
- `about-section`
- `service-section`
- `service-card`
- `what-we-create-section`
- `what-we-create-tab`
- `category-tab`
- `why-choose-us-section`
- `why-choose-us-tab`
- `our-facts-section`
- `our-fact`
- `our-promise`
- `process-step`

**Actions**:
- `created` - New content was created
- `updated` - Existing content was updated
- `deleted` - Content was deleted

## Performance Considerations

1. **Polling Interval**: Adjust the polling interval based on your needs:
   - 1-2 seconds: More real-time, higher server load
   - 5-10 seconds: Balanced
   - 30+ seconds: Lower server load, less real-time

2. **Selective Refresh**: Instead of refreshing all content, refresh only the section that changed based on `content_type`.

3. **Throttling**: Implement client-side throttling to prevent multiple rapid refresh calls.

## Testing

1. Open your website in a browser
2. Open browser developer tools (F12)
3. Make a change in the admin panel
4. Watch the console - you should see update messages
5. Content should refresh automatically within the polling interval

## Troubleshooting

### Updates not showing:
1. Check browser console for errors
2. Verify the API endpoint is accessible
3. Check network tab to see if polling requests are being made
4. Verify the timestamp is actually changing on the backend

### Too many requests:
- Increase the polling interval
- Implement request throttling
- Use WebSockets instead of polling

## Next Steps

1. Implement the polling mechanism in your frontend
2. Test with actual content updates
3. Adjust polling interval based on performance
4. Optionally implement WebSockets for true real-time updates

