import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'fr.mondary.tarotdivinatoire',
  appName: 'Tarot Divinatoire',
  webDir: 'src/mobile/www',
  bundledWebRuntime: false,
  ios: { contentInset: 'always' },
  android: { path: 'Android', allowMixedContent: false, adjustMarginsForEdgeToEdge: 'force', backgroundColor: '#0a0907' }
};

export default config;
