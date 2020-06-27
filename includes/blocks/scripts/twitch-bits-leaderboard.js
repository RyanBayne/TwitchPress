var el = wp.element.createElement,
    registerBlockType = wp.blocks.registerBlockType,
    blockStyle = { backgroundColor: '#900', color: '#fff', padding: '20px' };

registerBlockType( 'twitchpress-block/twitch-bits-leaderboard', {
    title: 'Bits Leaderboard',

    icon: 'universal-access-alt',

    category: 'twitchpress_block_category',
    
    edit: function() {
        return el( 'p', { style: blockStyle }, 'TwitchPress Placeholder Under Construction - Display a leaderboard of your bit donators!' );
    },

    save: function() {
        return el( 'p', { style: blockStyle }, 'TwitchPress Placeholder Under Construction - Display a leaderboard of your bit donators!' );
    },
} ); 