# Android Capacitor Build Notes

This Android project is a Capacitor shell for the Laravel `/mobile` product.
The Laravel web app remains the source of truth for authentication, routing, data,
and server-rendered screens.

## Local Emulator Target

By default, `capacitor.config.ts` loads:

```text
http://10.0.2.2:8000/mobile
```

That is the Android emulator alias for the host machine. Start Laravel locally:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

Then sync/build the Android project:

```powershell
npx cap sync android
npm run android:build
```

## Release Target

Set the hosted mobile URL before syncing:

```powershell
$env:CAPACITOR_SERVER_URL = "https://your-production-domain.example/mobile"
npx cap sync android
```

The Android app appends `AromaMadeMobile` to the WebView user agent. Laravel can
use that marker through the existing `mobile.app` middleware when app-only access
is desired.

## Java Runtime

The generated Android project requires Java 11 or newer. On this workstation,
Gradle initially picked up Android Studio's old bundled Java 8 runtime through
`JAVA_HOME`. This command built successfully:

```powershell
$env:JAVA_HOME = "C:\Program Files\Eclipse Adoptium\jdk-21.0.9.10-hotspot"
npm run android:build
```

The debug APK is generated at:

```text
android/app/build/outputs/apk/debug/app-debug.apk
```
