# Controller Update Guide for Real-Time Broadcasting

This guide explains how to add real-time broadcasting to all admin controllers.

## What Has Been Implemented

1. ✅ **ContentUpdated Event** - Created and configured
2. ✅ **BroadcastsContentUpdates Trait** - Created for easy reuse
3. ✅ **Content Update API Endpoint** - `/api/v1/content-last-updated`
4. ✅ **Broadcasting Channel** - Public `content-updates` channel configured
5. ✅ **Example Controllers Updated**:
   - `OurFactsSectionController`
   - `OurFactController`

## How to Add Broadcasting to Any Controller

### Step 1: Add the Trait

At the top of your controller file, add:

```php
use App\Traits\BroadcastsContentUpdates;

class YourController extends Controller
{
    use BroadcastsContentUpdates;
    // ...
}
```

### Step 2: Add Broadcasting in Create Method

After creating a record:

```php
$record = YourModel::create($validated);

// Broadcast content update
$this->broadcastContentUpdate('content-type-name', 'created', [
    'id' => $record->id,
]);
```

### Step 3: Add Broadcasting in Update Method

After updating a record:

```php
$record->fill($dataToUpdate);
$record->save();
$record->refresh();

// Broadcast content update
$this->broadcastContentUpdate('content-type-name', 'updated', [
    'id' => $record->id,
]);
```

### Step 4: Add Broadcasting in Delete Method

Before deleting, capture the ID, then broadcast:

```php
$recordId = $record->id;
$record->delete();

// Broadcast content update
$this->broadcastContentUpdate('content-type-name', 'deleted', [
    'id' => $recordId,
]);
```

### Step 5: Add Broadcasting in Delete Field Method (if applicable)

After deleting a field:

```php
$section->$field = null;
$section->save();
$section->refresh();

// Broadcast content update
$this->broadcastContentUpdate('content-type-name', 'updated', [
    'id' => $section->id,
    'field' => $field,
]);
```

## Content Type Names to Use

Use these consistent content type names when broadcasting:

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

## Controllers That Need Updates

Apply the above steps to these controllers:

- [ ] `HomePageController`
- [ ] `AboutSectionController`
- [ ] `ServiceSectionController`
- [ ] `ServiceCardController`
- [ ] `WhatWeCreateSectionController`
- [ ] `WhatWeCreateTabController`
- [ ] `CategoryTabController`
- [ ] `WhyChooseUsSectionController`
- [ ] `WhyChooseUsTabController`
- [x] `OurFactsSectionController` (Done)
- [x] `OurFactController` (Done)
- [ ] `OurPromiseController`
- [ ] `ProcessStepController`

## Quick Example: OurPromiseController

```php
<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\OurPromise;
use App\Traits\BroadcastsContentUpdates; // Add this
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OurPromiseController extends Controller
{
    use BroadcastsContentUpdates; // Add this

    public function store(Request $request)
    {
        // ... validation ...

        $promise = OurPromise::updateOrCreate(
            ['id' => OurPromise::first()?->id ?? 0],
            $validated
        );

        // Add this:
        $this->broadcastContentUpdate('our-promise', 'updated', [
            'id' => $promise->id,
        ]);

        return response()->json([...]);
    }

    public function update(Request $request, $id)
    {
        // ... existing code ...

        if (!empty($dataToUpdate)) {
            $promise->fill($dataToUpdate);
            $promise->save();
            $promise->refresh();

            // Add this:
            $this->broadcastContentUpdate('our-promise', 'updated', [
                'id' => $promise->id,
            ]);
        }

        return response()->json([...]);
    }

    public function destroy(Request $request, $id)
    {
        // ... existing code ...

        $promiseId = $promise->id;
        $promise->delete();

        // Add this:
        $this->broadcastContentUpdate('our-promise', 'deleted', [
            'id' => $promiseId,
        ]);

        return response()->json([...]);
    }
}
```

## Configuration

Make sure these are configured:

1. **BroadcastServiceProvider** is enabled in `config/app.php`
2. **Broadcast Driver** is set in `.env`:
   ```
   BROADCAST_DRIVER=log  # For testing (logs events)
   # Or:
   BROADCAST_DRIVER=redis  # For production with Redis
   ```
3. **Channel** is registered in `routes/channels.php` (already done)

## Testing

1. Open browser console on your website
2. Make a change in admin panel
3. Check console/logs - you should see broadcast events
4. Content should refresh automatically (if frontend is configured)

## Notes

- Broadcasting works even with `BROADCAST_DRIVER=log` - events are logged and timestamp is still updated
- For production, use Redis or Pusher for actual WebSocket broadcasting
- The timestamp update works regardless of broadcast driver - frontend can poll the API endpoint

