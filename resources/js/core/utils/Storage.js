/**
 * Storage Utility
 * Handles localStorage and sessionStorage with namespace, TTL, and JSON parsing.
 */

const NAMESPACE = 'SikapApp_';

export const Storage = {
    /**
     * Save data to storage
     * @param {string} key 
     * @param {any} value 
     * @param {string} type 'local' or 'session'
     * @param {number} ttlInMinutes Time to live in minutes (optional)
     */
    set(key, value, type = 'local', ttlInMinutes = null) {
        const storage = type === 'session' ? sessionStorage : localStorage;
        const item = {
            value: value,
            timestamp: new Date().getTime(),
            ttl: ttlInMinutes ? ttlInMinutes * 60 * 1000 : null
        };
        try {
            storage.setItem(NAMESPACE + key, JSON.stringify(item));
        } catch (e) {
            console.warn('Storage limit exceeded or unavailable.', e);
        }
    },

    /**
     * Get data from storage
     * @param {string} key 
     * @param {string} type 'local' or 'session'
     * @param {any} defaultValue Default value if not found or expired
     */
    get(key, type = 'local', defaultValue = null) {
        const storage = type === 'session' ? sessionStorage : localStorage;
        const itemStr = storage.getItem(NAMESPACE + key);
        
        if (!itemStr) return defaultValue;

        try {
            const item = JSON.parse(itemStr);
            
            // Check TTL
            if (item.ttl && (new Date().getTime() - item.timestamp > item.ttl)) {
                this.remove(key, type);
                return defaultValue;
            }
            return item.value;
        } catch (e) {
            return defaultValue;
        }
    },

    /**
     * Remove item from storage
     * @param {string} key 
     * @param {string} type 'local' or 'session'
     */
    remove(key, type = 'local') {
        const storage = type === 'session' ? sessionStorage : localStorage;
        storage.removeItem(NAMESPACE + key);
    },

    /**
     * Clear all namespaced items
     * @param {string} type 'local', 'session', or 'both'
     */
    clear(type = 'both') {
        if (type === 'local' || type === 'both') {
            Object.keys(localStorage).forEach(key => {
                if (key.startsWith(NAMESPACE)) localStorage.removeItem(key);
            });
        }
        if (type === 'session' || type === 'both') {
            Object.keys(sessionStorage).forEach(key => {
                if (key.startsWith(NAMESPACE)) sessionStorage.removeItem(key);
            });
        }
    }
};
