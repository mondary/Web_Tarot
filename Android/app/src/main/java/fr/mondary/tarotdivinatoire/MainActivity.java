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
                    "(function(){var d=document.querySelector('dialog[open]');if(d){d.close();return true;}var v=document.querySelector('#rituel');if(v&&v.hidden){document.querySelector('[data-view=rituel]').click();return true;}return false;})()",
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
