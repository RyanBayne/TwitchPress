var el = wp.element.createElement,
    registerBlockType = wp.blocks.registerBlockType,
    blockStyle = { backgroundColor: '#900', color: '#fff', padding: '20px' };

registerBlockType( 'twitchpress-block/twitch-giving-channel-chat', {
    title: 'Twitch Channel Chat',

    icon: 'universal-access-alt',

    category: 'twitchpress_block_category',
    
    edit: function() {
        return el( 'p', { style: blockStyle }, 'TwitchPress Placeholder Under Construction - Display the chat for any giving channel!' );
    },

    save: function() {
        return el( 'p', { style: blockStyle }, 'TwitchPress Placeholder Under Construction - Display the chat for any giving channel!' );
    },
} );