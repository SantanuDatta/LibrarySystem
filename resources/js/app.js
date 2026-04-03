document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-menu-toggle]').forEach((button) => {
        const controls = button.getAttribute('aria-controls');

        if (!controls) {
            return;
        }

        const menu = document.getElementById(controls);

        if (!menu) {
            return;
        }

        button.addEventListener('click', () => {
            const isExpanded = button.getAttribute('aria-expanded') === 'true';

            button.setAttribute('aria-expanded', String(!isExpanded));
            menu.hidden = isExpanded;

            const openIcon = button.querySelector('[data-menu-icon="open"]');
            const closeIcon = button.querySelector('[data-menu-icon="close"]');

            if (openIcon) {
                openIcon.hidden = !isExpanded;
            }

            if (closeIcon) {
                closeIcon.hidden = isExpanded;
            }
        });
    });
});
