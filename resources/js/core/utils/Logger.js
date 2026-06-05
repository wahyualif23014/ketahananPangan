/**
 * Logger Utility
 * Centralized logging that can be disabled in production
 */

// Enable logging only if not in production
const isDebug = import.meta.env.VITE_APP_ENV !== 'production';

export const Logger = {
    info(message, ...args) {
        if (isDebug) console.log(`%c[SIKAP INFO]%c ${message}`, 'color: #10b981; font-weight: bold;', 'color: inherit;', ...args);
    },
    warn(message, ...args) {
        if (isDebug) console.warn(`%c[SIKAP WARN]%c ${message}`, 'color: #f59e0b; font-weight: bold;', 'color: inherit;', ...args);
    },
    error(message, ...args) {
        // Errors should always be logged, or reported to an error tracking service
        console.error(`%c[SIKAP ERROR]%c ${message}`, 'color: #ef4444; font-weight: bold;', 'color: inherit;', ...args);
    },
    debug(message, ...args) {
        if (isDebug) console.debug(`%c[SIKAP DEBUG]%c ${message}`, 'color: #3b82f6; font-weight: bold;', 'color: inherit;', ...args);
    }
};
