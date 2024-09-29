# Image Rendering - why async=true is good default behavior

Before 10/2024, we had the following snippet active in all our projects:

```fusion
prototype(Sitegeist.Kaleidoscope:AssetImageSource) {
    async = false
}
```

The explanation for this was as follows (**OUTDATED / WRONG AS WE UNDERSTAND NOW**):

```
// WORKAROUND: AssetImageSource sets async=true by default.
// this means: Instead of rendering the images during page render, image URLs are like /media/thumbnail/[uuid]
// and are rendered by PHP on request (if not existing). This spins up a PHP interpreter for EVERY image fetch,
// which is what we want to avoid.
//
// With async=false, the page will be slow for the unlucky person who arrives there when no images are rendered,
// as lots of images will be rendered then (all srcsets of all images on the page); but as this is not just stored
// in the content cache, but persisted (on disk and on DB) this will happen extremely rarely.

```

# Problems with async=false

Sometime we have image galleries consisting of 20+ images; each with jpg/webp, and each with 5-10 srcset variants -> so this
can easily lead to 20*10*2 = 400 resizing/conversion operations to be done during initial render.

This eats our memory and CPU, and the user gets timeouts then (or the server crashes due to lack of resources).

Additionally this is a problem which happens **deterministically**, i.e. on the next request to the page, the same
happens again (as thumbnails are not rendered) - because already-rendered thumbnail resources are NOT persisted until
everything is finished.

# async=true does the following:

```
              ┌───────────────────┐                ┌───────────────────┐
              │ Fusion: Page not  │                │  Fusion: Page in  │
              │    yet cached     │                │       cache       │
              └───────────────────┘                └───────────────────┘
                        │                                    │          
              ┌─────────▼─────────┐                ┌─────────▼─────────┐
              │does the thumbnail │                │some thumbnail URLs│
              │  exist on disk?   │                │ with /_Resources/ │
              ├───────────────────┤                │   and some with   │
           ┌──┘                   └──┐             │ /media/thumbnail/ │
           │                         │             └───────────────────┘
           ▼                         ▼                                  
╔═════════════════════╗   ┌─────────────────────┐                       
║     YES: URL to     ║   │     NO: URL to      │                       
║/_Resources/... (does║   │/media/thumbnail/... │                       
║  not trigger PHP)   ║   │   (TRIGGERS PHP)    │                       
╚═══════════╦═════════╝   └─────────┬───────────┘                       
            │                       │                                   
┌───────────▼───────────────────────▼───────────┐                       
│   the above URLs are stored in Fusion cache   │                       
└───────────────────────────────────────────────┘                                           
```

For determining server load etc, it is important that we do NOT trigger a PHP request for every image on every page load.
Instead, images should be delivered by the web server directly.

=> The mechanism of async=true from Neos core converges to the (good) situation that all images are served without
triggering PHP (_Resources/Persistent), although this might take up to 24h (Fusion cache lifetime) for newly uploaded
images without thumbnails.

So this means:

- You upload a new image (fusion cache of this page is cleared, no thumbnails on disk yet)
- Initial fusion render -> image URLs with /media/thumbnail/... -> stored in Fusion Cache
  - -> Web Browser calls PHP which then (on first run) renders thumbnail.
- !! when the fusion cache is cleared the next time (happens very often when you change content on a page), maximum
  every 24 hours, the next fusion rendering will contain links to /_Resources, which does NOT trigger PHP anymore.

!! So do not clear the `neos_media_domain_model_thumbnail` table, as this would lead to ALL images being PHP rendered,
and if you do, make sure to frequently clear the fusion cache.


# In The Long Run => likely imagor

the scenario above is quite complex, we think it will become with less moving parts if we outsource image handling to Imagor.
