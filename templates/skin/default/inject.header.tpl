        <meta property="fb:app_id" content="{cfg name="plugin.facebook.application.id"}" />
        <meta property="og:type" content="artile" />
        {if $sTitle}<meta property="og:title" content="{$sTitle}" />{/if}

        {if $sImage}<meta property="og:image" content="{$sImage}" />{/if}
        
        {if $aVideo}
        <meta property="og:image" content="{$aVideo.picture}" />
        <meta property="og:video" content="{$aVideo.source}" />
        <meta property="og:video:type" content="{$aVideo.videotype}" />
        <meta property="og:video:width" content="{$aVideo.width}" />
        <meta property="og:video:height" content="{$aVideo.height}" />
        {/if}
