var el = wp.element.createElement,
    registerBlockType = wp.blocks.registerBlockType,
    blockStyle = { backgroundColor: '#900', color: '#fff', padding: '20px' };

registerBlockType( 'twitchpress-block/twitch-top-games-list', {
    title: 'Top Games List',

    icon: 'universal-access-alt',

    category: 'twitchpress_block_category',
    
    edit: function() {
        return el( 'p', { style: blockStyle }, 'TwitchPress Placeholder Under Construction - Display a list of Twitch.tv top games!' );
    },

    save: function() {
        return el( 'p', { style: blockStyle }, 'TwitchPress Placeholder Under Construction - Display a list of Twitch.tv top games!' );
    },
} );