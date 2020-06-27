var el = wp.element.createElement,
    registerBlockType = wp.blocks.registerBlockType,
    blockStyle = { backgroundColor: '#900', color: '#fff', padding: '20px' };

registerBlockType( 'twitchpress-block/twitch-members-only', {
    title: 'Twitch Members Only',

    icon: 'universal-access-alt',

    category: 'twitchpress_block_category',
    
    edit: function() {
        return el( 'p', { style: blockStyle }, 'TwitchPress Placeholder Under Construction - This block will allow members only content to be inserted between other content!' );
    },

    save: function() {
        return el( 'p', { style: blockStyle }, 'TwitchPress Placeholder Under Construction - This block will allow members only content to be inserted between other content!' );
    },
} );