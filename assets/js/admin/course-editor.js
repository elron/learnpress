;

/**
 * Helpers
 *
 * @since 3.0.0
 */
(function (exports) {
    function cloneObject(object) {
        return JSON.parse(JSON.stringify(object));
    }

    exports.LP_Helpers = {
        cloneObject: cloneObject
    };
})(window);


/**
 * I18n Store
 *
 * @since 3.0.0
 */

var LP_Curriculum_i18n_Store = (function (Vue, helpers, data) {
    var state = helpers.cloneObject(data.i18n);

    var getters = {
        all: function (state) {
            return state;
        }
    };

    return {
        namespaced: true,
        state: state,
        getters: getters
    };

})(Vue, LP_Helpers, lq_course_editor);

/**
 * Sections Store.
 *
 * @since 3.0.0
 */
var LP_Curriculum_Sections_Store = (function (Vue, helpers, data) {
    var state = helpers.cloneObject(data.sections);

    state.statusUpdateSection = {};
    state.statusUpdateSectionItem = {};

    state.sections = state.sections.map(function (section) {
        var hiddenSections = state.hidden_sections;
        var find = hiddenSections.find(function (sectionId) {
            return parseInt(section.id) === parseInt(sectionId);
        });

        section.open = !find;

        return section;
    });

    var getters = {
        sections: function (state) {
            return state.sections || [];
        },
        urlEdit: function (state) {
            return state.urlEdit;
        },
        hiddenSections: function (state) {
            return state.sections
                .filter(function (section) {
                    return !section.open;
                })
                .map(function (section) {
                    return parseInt(section.id);
                });
        },
        isHiddenAllSections: function (state, getters) {
            var sections = getters['sections'];
            var hiddenSections = getters['hiddenSections'];

            return hiddenSections.length === sections.length;
        },
        statusUpdateSection: function (state) {
            return state.statusUpdateSection;
        },
        statusUpdateSectionItem: function (state) {
            return state.statusUpdateSectionItem;
        }
    };

    var mutations = {
        'SORT_SECTION': function (state, orders) {
            state.sections = state.sections.map(function (section) {
                section.order = orders[section.id];

                return section;
            });
        },
        'SET_SECTIONS': function (state, sections) {
            state.sections = sections;
        },
        'ADD_NEW_SECTION': function (state, section) {
            section.open = true;
            state.sections.push(section);
        },
        'REMOVE_SECTION': function (state, index) {
            state.sections.splice(index, 1);
        },
        'REMOVE_SECTION_ITEM': function (state, payload) {
            var section = state.sections.find(function (section) {
                return (section.id === payload.section_id);
            });

            var items = section.items || [];
            var index = -1;
            items.forEach(function (item, i) {
                if (item.id === payload.item_id) {
                    index = i;
                }
            });

            if (index !== -1) {
                items.splice(index, 1);
            }
        },
        'UPDATE_SECTION_ITEMS': function (state, payload) {
            var section = state.sections.find(function (section) {
                return parseInt(section.id) === parseInt(payload.section_id);
            });

            if (!section) {
                return;
            }
            section.items = payload.items;
        },
        'UPDATE_SECTION_ITEM': function (state, payload) {

        },

        'CLOSE_SECTION': function (state, section) {
            state.sections.forEach(function (_section, index) {
                if (section.id === _section.id) {
                    state.sections[index].open = false;
                }
            });

        },

        'OPEN_SECTION': function (state, section) {
            state.sections.forEach(function (_section, index) {
                if (section.id === _section.id) {
                    state.sections[index].open = true;
                }
            });
        },

        'OPEN_ALL_SECTIONS': function (state) {
            state.sections = state.sections.map(function (_section) {
                _section.open = true;

                return _section;
            });
        },

        'CLOSE_ALL_SECTIONS': function (state) {
            state.sections = state.sections.map(function (_section) {
                _section.open = false;

                return _section;
            });
        },

        'UPDATE_SECTION_REQUEST': function (state, sectionId) {
            Vue.set(state.statusUpdateSection, sectionId, 'updating');
        },

        'UPDATE_SECTION_SUCCESS': function (state, sectionId) {
            Vue.set(state.statusUpdateSection, sectionId, 'successful');
        },

        'UPDATE_SECTION_FAILURE': function (state, sectionId) {
            Vue.set(state.statusUpdateSection, sectionId, 'failed');
        },

        'UPDATE_SECTION_ITEM_REQUEST': function (state, itemId) {
            Vue.set(state.statusUpdateSectionItem, itemId, 'updating');
        },

        'UPDATE_SECTION_ITEM_SUCCESS': function (state, itemId) {
            Vue.set(state.statusUpdateSectionItem, itemId, 'successful');
        },

        'UPDATE_SECTION_ITEM_FAILURE': function (state, itemId) {
            Vue.set(state.statusUpdateSectionItem, itemId, 'failed');
        }
    };

    var actions = {

        toggleAllSections: function (context) {
            var hidden = context.getters['isHiddenAllSections'];

            if (hidden) {
                context.commit('OPEN_ALL_SECTIONS');
            } else {
                context.commit('CLOSE_ALL_SECTIONS');
            }

            Vue.http.LPRequest({
                type: 'hidden-sections',
                hidden: context.getters['hiddenSections']
            });
        },

        updateSectionsOrder: function (context, order) {
            Vue.http.LPRequest({
                type: 'sort-sections',
                order: JSON.stringify(order)
            }).then(
                function (response) {
                    var result = response.body;
                    var order_sections = result.data;
                    context.commit('SORT_SECTION', order_sections);
                },
                function (error) {
                    console.error(error);
                }
            );
        },

        toggleSection: function (context, section) {
            if (section.open) {
                context.commit('CLOSE_SECTION', section);
            } else {
                context.commit('OPEN_SECTION', section);
            }

            Vue.http.LPRequest({
                type: 'hidden-sections',
                hidden: context.getters['hiddenSections']
            });
        },

        updateSection: function (context, section) {
            context.commit('UPDATE_SECTION_REQUEST', section.id);

            Vue.http
                .LPRequest({
                    type: 'update-section',
                    section: JSON.stringify(section)
                })
                .then(function () {
                    context.commit('UPDATE_SECTION_SUCCESS', section.id);
                })
                .catch(function () {
                    context.commit('UPDATE_SECTION_FAILURE', section.id);
                })
        },

        removeSection: function (context, payload) {
            context.commit('REMOVE_SECTION', payload.index);

            Vue.http.LPRequest({
                type: 'remove-section',
                section_id: payload.section.id
            }).then(
                function (response) {
                    var result = response.body;
                },
                function (error) {
                    console.error(error);
                }
            );
        },

        newSection: function (context, name) {
            Vue.http.LPRequest({
                type: 'new-section',
                section_name: name
            }).then(
                function (response) {
                    var result = response.body;

                    if (result.success) {
                        context.commit('ADD_NEW_SECTION', result.data);
                    }
                },
                function (error) {
                    console.error(error);
                }
            );
        },

        updateSectionItem: function (context, payload) {
            context.commit('UPDATE_SECTION_ITEM_REQUEST', payload.item.id);

            Vue.http.LPRequest({
                type: 'update-section-item',
                section_id: payload.section_id,
                item: JSON.stringify(payload.item)

            }).then(
                function (response) {
                    context.commit('UPDATE_SECTION_ITEM_SUCCESS', payload.item.id);

                    var result = response.body;
                    if (result.success) {
                        var item = result.data;

                        context.commit('UPDATE_SECTION_ITEM', {section_id: payload.section_id, item: item});
                    }
                },
                function (error) {
                    context.commit('UPDATE_SECTION_ITEM_FAILURE', payload.item.id);
                    console.error(error);
                }
            );
        },

        removeSectionItem: function (context, payload) {
            context.commit('REMOVE_SECTION_ITEM', payload);

            Vue.http
                .LPRequest({
                    type: 'remove-section-item',
                    section_id: payload.section_id,
                    item_id: payload.item_id
                });
        },

        newSectionItem: function (context, payload) {
            Vue.http.LPRequest({
                type: 'new-section-item',
                section_id: payload.section_id,
                item: JSON.stringify(payload.item)
            }).then(
                function (response) {
                    var result = response.body;

                    if (result.success) {
                        context.commit('UPDATE_SECTION_ITEMS', {section_id: payload.section_id, items: result.data});
                    }
                },
                function (error) {
                    console.error(error);
                }
            );
        },

        updateSectionItems: function (context, payload) {
            Vue.http.LPRequest({
                type: 'update-section-items',
                section_id: payload.section_id,
                items: JSON.stringify(payload.items)
            }).then(
                function (response) {
                    var result = response.body;

                    if (result.success) {
                        // console.log(result);
                    }
                },
                function (error) {
                    console.error(error);
                }
            );
        }
    };

    return {
        namespaced: true,
        state: state,
        getters: getters,
        mutations: mutations,
        actions: actions
    };
})(Vue, LP_Helpers, lq_course_editor);


/**
 * Choose Item Modal Store
 *
 * @since 3.0.0
 *
 * @type {{namespaced, state, getters, mutations, actions}}
 */
var LP_Choose_Items_Modal_Store = (function (exports, Vue, helpers, data) {
    var state = helpers.cloneObject(data.chooseItems);
    state.sectionId = false;
    state.pagination = '';
    state.status = '';

    var getters = {
        status: function (state) {
            return state.status;
        },
        pagination: function (state) {
            return state.pagination;
        },
        items: function (state, _getters) {
            return state.items.map(function (item) {
                var find = _getters.addedItems.find(function (_item) {
                    return item.id === _item.id;
                });

                item.added = !!find;

                return item;
            });
        },
        addedItems: function (state) {
            return state.addedItems;
        },
        isOpen: function (state) {
            return state.open;
        },
        types: function (state) {
            return state.types;
        },
        section: function () {
            return state.sectionId;
        }
    };

    var mutations = {
        'TOGGLE': function (state) {
            state.open = !state.open;
        },
        'SET_SECTION': function (state, sectionId) {
            state.sectionId = sectionId;
        },
        'SET_LIST_ITEMS': function (state, items) {
            state.items = items;
        },
        'ADD_ITEM': function (state, item) {
            state.addedItems.push(item);
        },
        'REMOVE_ADDED_ITEM': function (state, item) {
            state.addedItems.forEach(function (_item, index) {
                if (_item.id === item.id) {
                    state.addedItems.splice(index, 1);
                }
            });
        },
        'RESET': function (state) {
            state.addedItems = [];
            state.items = [];
        },
        'UPDATE_PAGINATION': function (state, pagination) {
            state.pagination = pagination;
        },
        'SEARCH_ITEMS_REQUEST': function (state) {
            state.status = 'loading';
        },
        'SEARCH_ITEMS_SUCCESS': function (state) {
            state.status = 'successful';
        },
        'SEARCH_ITEMS_FAILURE': function (state) {
            state.status = 'failed';
        }
    };

    var actions = {

        toggle: function (context) {
            context.commit('TOGGLE');
        },

        open: function (context, sectionId) {
            context.commit('SET_SECTION', sectionId);
            context.commit('RESET');
            context.commit('TOGGLE');
        },

        searchItems: function (context, payload) {
            context.commit('SEARCH_ITEMS_REQUEST');

            Vue.http.LPRequest({
                type: 'search-items',
                query: payload.query,
                item_type: payload.type,
                page: payload.page,
                exclude: JSON.stringify([])
            }).then(
                function (response) {
                    var result = response.body;

                    if (!result.success) {
                        return;
                    }

                    var data = result.data;

                    context.commit('SET_LIST_ITEMS', data.items);
                    context.commit('UPDATE_PAGINATION', data.pagination);
                    context.commit('SEARCH_ITEMS_SUCCESS');
                },
                function (error) {
                    context.commit('SEARCH_ITEMS_FAILURE');

                    console.error(error);
                }
            );
        },

        addItem: function (context, item) {
            context.commit('ADD_ITEM', item);
        },

        removeItem: function (context, index) {
            context.commit('REMOVE_ADDED_ITEM', index);
        },

        addItemsToSection: function (context) {
            var items = context.getters.addedItems;

            if (items.length > 0) {
                Vue.http.LPRequest({
                    type: 'add-items-to-section',
                    section_id: context.getters.section,
                    items: JSON.stringify(items)
                }).then(
                    function (response) {
                        var result = response.body;

                        if (result.success) {
                            context.commit('TOGGLE');

                            var items = result.data;
                            context.commit('ss/UPDATE_SECTION_ITEMS', {
                                section_id: context.getters.section,
                                items: items
                            }, {root: true});
                        }
                    },
                    function (error) {
                        console.error(error);
                    }
                );
            }
        }
    };

    return {
        namespaced: true,
        state: state,
        getters: getters,
        mutations: mutations,
        actions: actions
    }
})(window, Vue, LP_Helpers, lq_course_editor);

/**
 * Root Store
 *
 * @since 3.0.0
 */
(function (exports, Vue, Vuex, helpers, data) {
    var state = helpers.cloneObject(data.root);

    state.status = 'success';
    state.heartbeat = true;
    state.countCurrentRequest = 0;

    var getters = {
        heartbeat: function (state) {
            return state.heartbeat;
        },
        action: function (state) {
            return state.action;
        },
        id: function (state) {
            return state.course_id;
        },
        status: function (state) {
            return state.status || 'error';
        },
        currentRequest: function (state) {
            return state.countCurrentRequest || 0;
        },
        urlAjax: function (state) {
            return state.ajax;
        },
        nonce: function (state) {
            return state.nonce;
        }
    };

    var mutations = {
        'UPDATE_HEART_BEAT': function (state, status) {
            state.heartbeat = !!status;
        },

        'UPDATE_STATUS': function (state, status) {
            state.status = status;
        },
        'INCREASE_NUMBER_REQUEST': function (state) {
            state.countCurrentRequest++;
        },
        'DECREASE_NUMBER_REQUEST': function (state) {
            state.countCurrentRequest--;
        }
    };

    var actions = {
        heartbeat: function (context) {
            Vue.http.LPRequest({
                type: 'heartbeat'
            }).then(
                function (response) {
                    var result = response.body;
                    context.commit('UPDATE_HEART_BEAT', !!result.success);
                },
                function (error) {
                    context.commit('UPDATE_HEART_BEAT', false);
                }
            );
        },

        newRequest: function (context) {
            context.commit('INCREASE_NUMBER_REQUEST');
            context.commit('UPDATE_STATUS', 'loading');

            window.onbeforeunload = function () {
                return '';
            }
        },

        requestComplete: function (context, status) {
            context.commit('DECREASE_NUMBER_REQUEST');

            if (context.getters.currentRequest === 0) {
                context.commit('UPDATE_STATUS', status);
                window.onbeforeunload = null;
            }
        }
    };

    exports.LP_Curriculum_Store = new Vuex.Store({
        state: state,
        getters: getters,
        mutations: mutations,
        actions: actions,
        modules: {
            ci: LP_Choose_Items_Modal_Store,
            i18n: LP_Curriculum_i18n_Store,
            ss: LP_Curriculum_Sections_Store
        }
    });

})(window, Vue, Vuex, LP_Helpers, lq_course_editor);

/**
 * HTTP
 *
 * @since 3.0.0
 */
(function (exports, Vue, $store) {
    Vue.http.LPRequest = function (payload) {
        payload['id'] = $store.getters.id;
        payload['nonce'] = $store.getters.nonce;
        payload['lp-ajax'] = $store.getters.action;

        return Vue.http.post($store.getters.urlAjax,
            payload,
            {
                emulateJSON: true,
                params: {
                    namespace: 'LPCurriculumRequest'
                }
            });
    };

    Vue.http.interceptors.push(function (request, next) {
        if (request.params['namespace'] !== 'LPCurriculumRequest') {
            next();
            return;
        }

        $store.dispatch('newRequest');

        next(function (response) {
            var body = response.body;
            var result = body.success || false;

            if (result) {
                $store.dispatch('requestComplete', 'success');
            } else {
                $store.dispatch('requestComplete', 'failed');
            }
        });
    });
})(window, Vue, LP_Curriculum_Store);

/**
 * Init app.
 *
 * @since 3.0.0
 */
(function ($, Vue, $store) {
    $(document).ready(function () {
        window.LP_Course_Editor = new Vue({
            el: '#admin-course-editor',
            template: '<lp-course-editor></lp-course-editor>'
        });
    });
})(jQuery, Vue, LP_Curriculum_Store);