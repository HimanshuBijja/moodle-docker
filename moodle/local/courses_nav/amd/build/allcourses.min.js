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
 * Matches the block_myoverview rendering approach.
 *
 * @module     local_courses_nav/allcourses
 * @copyright  2026 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['local_courses_nav/repository', 'core/templates', 'core/notification'],
function(Repository, Templates, Notification) {

    // State.
    var currentPage = 0;
    var currentSearch = '';
    var currentSort = 'fullname';
    var currentViewMode = 'card';
    var currentClassification = 'all';
    var currentPerPage = 12;
    var totalCourses = 0;
    var isLoading = false;

    /**
     * Get the root element.
     * @param {string} rootId The root element ID.
     * @return {HTMLElement} The root element.
     */
    var getRoot = function(rootId) {
        return document.getElementById(rootId);
    };

    /**
     * Show a region.
     * @param {HTMLElement} root Root element.
     * @param {string} region Region name.
     */
    var showRegion = function(root, region) {
        var el = root.querySelector('[data-region="' + region + '"]');
        if (el) {
            el.classList.remove('d-none');
        }
    };

    /**
     * Hide a region.
     * @param {HTMLElement} root Root element.
     * @param {string} region Region name.
     */
    var hideRegion = function(root, region) {
        var el = root.querySelector('[data-region="' + region + '"]');
        if (el) {
            el.classList.add('d-none');
        }
    };

    /**
     * Get the template name for the current view mode.
     * @return {string} Template name.
     */
    var getTemplateName = function() {
        if (currentViewMode === 'list') {
            return 'local_courses_nav/course_list_item';
        } else if (currentViewMode === 'summary') {
            return 'local_courses_nav/course_summary_item';
        }
        return 'local_courses_nav/course_card';
    };

    /**
     * Render courses into the container.
     * @param {HTMLElement} root Root element.
     * @param {Array} courses Courses to render.
     * @return {Promise}
     */
    var renderCourses = function(root, courses) {
        var viewContent = root.querySelector('[data-region="course-view-content"]');
        if (!viewContent) {
            return Promise.resolve();
        }

        if (courses.length === 0) {
            // Show no-courses message matching core_course/no-courses style.
            viewContent.innerHTML = '<div class="text-center py-5">' +
                '<i class="fa fa-graduation-cap fa-3x text-muted mb-3 d-block"></i>' +
                '<p class="text-muted">No courses found</p>' +
                '</div>';
            hideRegion(root, 'paging-bar');
            return Promise.resolve();
        }

        var templateName = getTemplateName();

        // Add course category display flag.
        courses = courses.map(function(course) {
            course.showcoursecategory = true;
            return course;
        });

        if (currentViewMode === 'card') {
            // Card view: use the card-grid wrapper like core_course/coursecards.
            viewContent.innerHTML = '';
            var gridContainer = document.createElement('div');
            gridContainer.className = 'card-grid mx-0 row row-cols-1 row-cols-sm-2 row-cols-lg-3';
            gridContainer.setAttribute('data-region', 'card-deck');
            gridContainer.setAttribute('role', 'list');
            viewContent.appendChild(gridContainer);

            var promises = courses.map(function(course) {
                return Templates.renderForPromise(templateName, course).then(function(result) {
                    var col = document.createElement('div');
                    col.className = 'col d-flex px-0 mb-2';
                    col.innerHTML = result.html;
                    gridContainer.appendChild(col);
                    Templates.runTemplateJS(result.js);
                }).catch(function(e) {
                    Notification.exception(e);
                });
            });

            return Promise.all(promises);
        } else {
            // List and summary views: use list-group wrapper.
            viewContent.innerHTML = '';
            var listContainer;
            if (currentViewMode === 'list') {
                listContainer = document.createElement('ul');
                listContainer.className = 'list-group';
            } else {
                listContainer = document.createElement('div');
                listContainer.setAttribute('role', 'list');
            }
            viewContent.appendChild(listContainer);

            var promises = courses.map(function(course) {
                return Templates.renderForPromise(templateName, course).then(function(result) {
                    Templates.appendNodeContents(listContainer, result.html, result.js);
                }).catch(function(e) {
                    Notification.exception(e);
                });
            });

            return Promise.all(promises);
        }
    };

    /**
     * Create a single page item for pagination.
     * @param {HTMLElement} root Root element.
     * @param {number} pageNum Page number (0-based).
     * @param {string} label Display label.
     * @return {HTMLElement} The li element.
     */
    var createPageItem = function(root, pageNum, label) {
        var li = document.createElement('li');
        li.className = 'page-item' + (pageNum === currentPage ? ' active' : '');
        var a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.textContent = label;
        if (pageNum !== currentPage) {
            a.addEventListener('click', function(e) {
                e.preventDefault();
                currentPage = pageNum;
                fetchCourses(root);
            });
        }
        li.appendChild(a);
        return li;
    };

    /**
     * Build page number pagination controls.
     * @param {HTMLElement} root Root element.
     */
    var renderPagination = function(root) {
        if (totalCourses <= 12 && currentPerPage !== 0) {
            hideRegion(root, 'paging-bar');
            return;
        }

        if (currentPerPage === 0) {
            showRegion(root, 'paging-bar');
            var pageNav0 = root.querySelector('[data-region="page-nav"]');
            if (pageNav0) {
                pageNav0.classList.add('d-none');
            }
            return;
        }

        showRegion(root, 'paging-bar');

        var totalPages = Math.ceil(totalCourses / currentPerPage);
        var pageNav = root.querySelector('[data-region="page-nav"]');
        var pageNumbers = root.querySelector('[data-region="page-numbers"]');

        if (totalPages <= 1) {
            if (pageNav) {
                pageNav.classList.add('d-none');
            }
            return;
        }

        if (pageNav) {
            pageNav.classList.remove('d-none');
        }

        if (!pageNumbers) {
            return;
        }

        pageNumbers.innerHTML = '';

        // Previous button.
        var prevLi = document.createElement('li');
        prevLi.className = 'page-item' + (currentPage === 0 ? ' disabled' : '');
        var prevA = document.createElement('a');
        prevA.className = 'page-link';
        prevA.href = '#';
        prevA.innerHTML = '&laquo;';
        prevA.setAttribute('aria-label', 'Previous');
        if (currentPage > 0) {
            prevA.addEventListener('click', function(e) {
                e.preventDefault();
                currentPage--;
                fetchCourses(root);
            });
        }
        prevLi.appendChild(prevA);
        pageNumbers.appendChild(prevLi);

        // Page numbers with ellipsis.
        var maxVisible = 5;
        var startPage = Math.max(0, currentPage - Math.floor(maxVisible / 2));
        var endPage = Math.min(totalPages - 1, startPage + maxVisible - 1);

        if (endPage - startPage < maxVisible - 1) {
            startPage = Math.max(0, endPage - maxVisible + 1);
        }

        if (startPage > 0) {
            pageNumbers.appendChild(createPageItem(root, 0, '1'));
            if (startPage > 1) {
                var ellipsis1 = document.createElement('li');
                ellipsis1.className = 'page-item disabled';
                ellipsis1.innerHTML = '<span class="page-link">...</span>';
                pageNumbers.appendChild(ellipsis1);
            }
        }

        for (var i = startPage; i <= endPage; i++) {
            pageNumbers.appendChild(createPageItem(root, i, String(i + 1)));
        }

        if (endPage < totalPages - 1) {
            if (endPage < totalPages - 2) {
                var ellipsis2 = document.createElement('li');
                ellipsis2.className = 'page-item disabled';
                ellipsis2.innerHTML = '<span class="page-link">...</span>';
                pageNumbers.appendChild(ellipsis2);
            }
            pageNumbers.appendChild(createPageItem(root, totalPages - 1, String(totalPages)));
        }

        // Next button.
        var nextLi = document.createElement('li');
        nextLi.className = 'page-item' + (currentPage >= totalPages - 1 ? ' disabled' : '');
        var nextA = document.createElement('a');
        nextA.className = 'page-link';
        nextA.href = '#';
        nextA.innerHTML = '&raquo;';
        nextA.setAttribute('aria-label', 'Next');
        if (currentPage < totalPages - 1) {
            nextA.addEventListener('click', function(e) {
                e.preventDefault();
                currentPage++;
                fetchCourses(root);
            });
        }
        nextLi.appendChild(nextA);
        pageNumbers.appendChild(nextLi);
    };

    /**
     * Fetch and display courses.
     * @param {HTMLElement} root Root element.
     */
    var fetchCourses = function(root) {
        if (isLoading) {
            return;
        }
        isLoading = true;

        // Show loading, hide content.
        var loadingPlaceholder = root.querySelector('[data-region="loading-placeholder-content"]');
        var viewContent = root.querySelector('[data-region="course-view-content"]');

        if (loadingPlaceholder) {
            loadingPlaceholder.classList.remove('d-none');
            loadingPlaceholder.setAttribute('aria-hidden', 'false');
        }
        hideRegion(root, 'paging-bar');

        Repository.getAllCourses({
            search: currentSearch,
            page: currentPage,
            perpage: currentPerPage,
            sort: currentSort,
            classification: currentClassification,
            category: 0
        }).then(function(result) {
            totalCourses = result.totalcount;
            if (loadingPlaceholder) {
                loadingPlaceholder.classList.add('d-none');
                loadingPlaceholder.setAttribute('aria-hidden', 'true');
            }
            return renderCourses(root, result.courses);
        }).then(function() {
            renderPagination(root);
            isLoading = false;
        }).catch(function(e) {
            if (loadingPlaceholder) {
                loadingPlaceholder.classList.add('d-none');
            }
            isLoading = false;
            Notification.exception(e);
        });
    };

    /**
     * Debounce helper.
     * @param {Function} func Function to debounce.
     * @param {number} wait Wait time in ms.
     * @return {Function} Debounced function.
     */
    var debounce = function(func, wait) {
        var timeout;
        return function() {
            var args = arguments;
            var context = this;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                func.apply(context, args);
            }, wait);
        };
    };

    /**
     * Register event listeners.
     * @param {HTMLElement} root Root element.
     */
    var registerEventListeners = function(root) {
        // Search input.
        var searchInput = root.querySelector('[data-action="search"]');
        var clearBtn = root.querySelector('[data-action="clearsearch"]');

        if (searchInput) {
            var debouncedSearch = debounce(function() {
                currentSearch = searchInput.value.trim();
                if (clearBtn) {
                    if (currentSearch.length > 0) {
                        clearBtn.classList.remove('d-none');
                    } else {
                        clearBtn.classList.add('d-none');
                    }
                }
                currentPage = 0;
                fetchCourses(root);
            }, 300);

            searchInput.addEventListener('input', debouncedSearch);
        }

        // Clear search.
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                currentSearch = '';
                clearBtn.classList.add('d-none');
                currentPage = 0;
                fetchCourses(root);
            });
        }

        // Grouping (classification) dropdown.
        root.querySelectorAll('[data-filter="grouping"]').forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                var newClassification = e.currentTarget.dataset.value;
                if (newClassification !== currentClassification) {
                    currentClassification = newClassification;
                    currentPage = 0;

                    // Update aria-current for active state.
                    root.querySelectorAll('[data-filter="grouping"]').forEach(function(s) {
                        s.removeAttribute('aria-current');
                    });
                    e.currentTarget.setAttribute('aria-current', 'true');

                    // Update button text.
                    var btn = root.querySelector('#groupingdropdown [data-active-item-text]');
                    if (btn) {
                        btn.textContent = e.currentTarget.textContent.trim();
                    }

                    fetchCourses(root);
                }
            });
        });

        // Sort dropdown.
        root.querySelectorAll('[data-filter="sort"]').forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                var newSort = e.currentTarget.dataset.value;
                if (newSort !== currentSort) {
                    currentSort = newSort;
                    currentPage = 0;

                    // Update aria-current.
                    root.querySelectorAll('[data-filter="sort"]').forEach(function(s) {
                        s.removeAttribute('aria-current');
                    });
                    e.currentTarget.setAttribute('aria-current', 'true');

                    // Update button text.
                    var sortBtn = root.querySelector('#sortingdropdown [data-active-item-text]');
                    if (sortBtn) {
                        sortBtn.textContent = e.currentTarget.textContent.trim();
                    }

                    fetchCourses(root);
                }
            });
        });

        // Display/view mode dropdown.
        root.querySelectorAll('[data-display-option="display"]').forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                var newMode = e.currentTarget.dataset.value;
                if (newMode !== currentViewMode) {
                    currentViewMode = newMode;

                    // Update aria-current.
                    root.querySelectorAll('[data-display-option="display"]').forEach(function(s) {
                        s.removeAttribute('aria-current');
                    });
                    e.currentTarget.setAttribute('aria-current', 'true');

                    // Update button text.
                    var viewBtn = root.querySelector('#displaydropdown [data-active-item-text]');
                    if (viewBtn) {
                        viewBtn.textContent = e.currentTarget.textContent.trim();
                    }

                    // Update data-display attribute on courses-view.
                    var coursesView = root.querySelector('[data-region="courses-view"]');
                    if (coursesView) {
                        coursesView.setAttribute('data-display', newMode);
                    }

                    fetchCourses(root);
                }
            });
        });

        // Per-page selector.
        root.querySelectorAll('[data-action="perpage"]').forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                var newPerPage = parseInt(e.currentTarget.dataset.value, 10);
                if (newPerPage !== currentPerPage) {
                    currentPerPage = newPerPage;
                    currentPage = 0;

                    // Update active state.
                    root.querySelectorAll('[data-action="perpage"]').forEach(function(s) {
                        s.classList.remove('active');
                    });
                    e.currentTarget.classList.add('active');

                    // Update button text.
                    var perpageBtn = root.querySelector('[data-region="perpage-button"]');
                    if (perpageBtn) {
                        perpageBtn.textContent = newPerPage === 0 ? 'All' : String(newPerPage);
                    }

                    // Update data-paging attribute.
                    var coursesView = root.querySelector('[data-region="courses-view"]');
                    if (coursesView) {
                        coursesView.setAttribute('data-paging', newPerPage);
                    }

                    fetchCourses(root);
                }
            });
        });
    };

    return {
        /**
         * Initialize the All Courses page.
         * @param {string} rootId The root element ID.
         */
        init: function(rootId) {
            var root = getRoot(rootId);
            if (!root) {
                return;
            }
            registerEventListeners(root);
            fetchCourses(root);
        }
    };
});
