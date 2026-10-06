package fr.mondary.tarotdivinatoire;

import android.os.Bundle;
import androidx.activity.OnBackPressedCallback;
import com.getcapacitor.BridgeActivity;

public class MainActivity extends BridgeActivity {
    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        getOnBackPressedDispatcher().addCallback(this, new OnBackPressedCallback(true) {
            @Override
            public void handleOnBackPressed() {
                bridge.getWebView().evaluateJavascript(
                    "(function(){if(document.querySelector('#sp-drawer.open,#sp-fan.open,#sp-cut.open,#sp-name-screen.open,#sp-spread.open,#sp-menu.open,#search.open,#learn.open,#nuances.open,#setPanel.open')){document.dispatchEvent(new KeyboardEvent('keydown',{key:'Escape',bubbles:true}));return true;}if(document.querySelector('#detail.open')){closeDetail();return true;}return false;})()",
                    handled -> {
                        if (!"true".equals(handled)) {
                            if (bridge.getWebView().canGoBack()) bridge.getWebView().goBack();
                            else finish();
                        }
                    });
            }
        });
    }
}
