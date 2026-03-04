// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * All Courses page main module.
 *
 * @module     local_courses_nav/allcourses
 * @copyright  2026 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getAllCourses} from 'local_courses_nav/repository';
import Templates from 'core/templates';
import Notification from 'core/notification';

const PERPAGE = 12;

let currentPage = 0;
let currentSearch = '';
let currentSort = 'fullname';
let currentViewMode = 'card';
let totalCourses = 0;
let allLoadedCourses = [];
let isLoading = false;

/**
 * Get the root element.
 * @param {string} rootId The root element ID.
 * @return {HTMLElement} The root element.
 */
const getRoot = (rootId) => document.getElementById(rootId);

/**
 * Show a region.
 * @param {HTMLElement} root Root element.
 * @param {string} region Region name.
 */
const showRegion = (root, region) => {
    const el = root.querySelector(`[data-region="${region}"]`);
    if (el) {
        el.classList.remove('d-none');
    }
};

/**
 * Hide a region.
 * @param {HTMLElement} root Root element.
 * @param {string} region Region name.
 */
const hideRegion = (root, region) => {
    const el = root.querySelector(`[data-region="${region}"]`);
    if (el) {
        el.classList.add('d-none');
    }
};

/**
 * Update the course count display.
 * @param {HTMLElement} root Root element.
 */
const updateCourseCount = (root) => {
    const countEl = root.querySelector('[data-region="course-count"]');
    if (countEl) {
        const loaded = allLoadedCourses.length;
        countEl.textContent = `Showing ${loaded} of ${totalCourses} courses`;
    }
    showRegion(root, 'course-info');
};

/**
 * Render courses into the container.
 * @param {HTMLElement} root Root element.
 * @param {Array} courses Courses to render.
 * @param {boolean} append Whether to append or replace.
 */
const renderCourses = async(root, courses, append = false) => {
    const itemsContainer = root.querySelector('[data-region="course-items"]');
    if (!itemsContainer) {
        return;
    }

    if (!append) {
        itemsContainer.innerHTML = '';
    }

    if (courses.length === 0 && !append) {
        hideRegion(root, 'course-list');
        showRegion(root, 'no-courses');
        hideRegion(root, 'pagination');
        return;
    }

    hideRegion(root, 'no-courses');
    showRegion(root, 'course-list');

    const templateName = currentViewMode === 'card'
        ? 'local_courses_nav/course_card'
        : 'local_courses_nav/course_list_item';

    // Set grid or list class.
    if (currentViewMode === 'card') {
        itemsContainer.className = 'allcourses-grid';
    } else {
        itemsContainer.className = 'allcourses-list border rounded';
    }

    for (const course of courses) {
        try {
            const {html, js} = await Templates.renderForPromise(templateName, course);
            Templates.appendNodeContents(itemsContainer, html, js);
        } catch (e) {
            Notification.exception(e);
        }
    }
};

/**
 * Fetch and display courses.
 * @param {HTMLElement} root Root element.
 * @param {boolean} loadMore Whether this is a "load more" action.
 */
const fetchCourses = async(root, loadMore = false) => {
    if (isLoading) {
        return;
    }
    isLoading = true;

    if (!loadMore) {
        currentPage = 0;
        allLoadedCourses = [];
        showRegion(root, 'loading');
        hideRegion(root, 'course-list');
        hideRegion(root, 'no-courses');
        hideRegion(root, 'pagination');
    }

    try {
        const result = await getAllCourses({
            search: currentSearch,
            page: currentPage,
            perpage: PERPAGE,
            sort: currentSort,
        });

        totalCourses = result.totalcount;
        const newCourses = result.courses;
        allLoadedCourses = allLoadedCourses.concat(newCourses);

        hideRegion(root, 'loading');

        if (loadMore) {
            await renderCourses(root, newCourses, true);
        } else {
            await renderCourses(root, allLoadedCourses, false);
        }

        updateCourseCount(root);

        // Show/hide load more button.
        if (allLoadedCourses.length < totalCourses) {
            showRegion(root, 'pagination');
        } else {
            hideRegion(root, 'pagination');
        }
    } catch (e) {
        hideRegion(root, 'loading');
        Notification.exception(e);
    } finally {
        isLoading = false;
    }
};

/**
 * Debounce helper.
 * @param {Function} func Function to debounce.
 * @param {number} wait Wait time in ms.
 * @return {Function} Debounced function.
 */
const debounce = (func, wait) => {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
};

/**
 * Register event listeners.
 * @param {HTMLElement} root Root element.
 */
const registerEventListeners = (root) => {
    // Search input.
    const searchInput = root.querySelector('[data-action="search"]');
    const clearSearch = root.querySelector('[data-region="clear-search"]');

    if (searchInput) {
        const debouncedSearch = debounce(() => {
            currentSearch = searchInput.value.trim();
            if (currentSearch.length > 0) {
                clearSearch.classList.remove('d-none');
            } else {
                clearSearch.classList.add('d-none');
            }
            fetchCourses(root);
        }, 300);

        searchInput.addEventListener('input', debouncedSearch);
    }

    // Clear search.
    const clearBtn = root.querySelector('[data-action="clearsearch"]');
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            searchInput.value = '';
            currentSearch = '';
            clearSearch.classList.add('d-none');
            fetchCourses(root);
        });
    }

    // Sort.
    root.querySelectorAll('[data-action="sort"]').forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const newSort = e.currentTarget.dataset.sort;
            if (newSort !== currentSort) {
                currentSort = newSort;
                // Update active state.
                root.querySelectorAll('[data-action="sort"]').forEach(s => s.classList.remove('active'));
                e.currentTarget.classList.add('active');
                // Update button text.
                const sortBtn = root.querySelector('[data-region="sort-button"]');
                if (sortBtn) {
                    const icon = currentSort === 'fullname'
                        ? '<i class="fa fa-sort-alpha-asc mr-1"></i>'
                        : '<i class="fa fa-clock-o mr-1"></i>';
                    sortBtn.innerHTML = icon + e.currentTarget.textContent.trim();
                }
                fetchCourses(root);
            }
        });
    });

    // View mode.
    root.querySelectorAll('[data-action="viewmode"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const newMode = btn.dataset.mode;
            if (newMode !== currentViewMode) {
                currentViewMode = newMode;
                root.querySelectorAll('[data-action="viewmode"]').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                // Re-render with all loaded courses.
                renderCourses(root, allLoadedCourses, false);
            }
        });
    });

    // Load more.
    const loadMoreBtn = root.querySelector('[data-action="loadmore"]');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', () => {
            currentPage++;
            fetchCourses(root, true);
        });
    }
};

/**
 * Initialize the All Courses page.
 * @param {string} rootId The root element ID.
 */
export const init = (rootId) => {
    const root = getRoot(rootId);
    if (!root) {
        return;
    }
    registerEventListeners(root);
    fetchCourses(root);
};
