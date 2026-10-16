import { registerGlobalComponent } from '@saucebase/vue/lib/globalComponents';
import ComingSoonBanner from './components/ComingSoonBanner.vue';

export function setup() {
    registerGlobalComponent('bottom', ComingSoonBanner);
}
