<?php

namespace Modera\NotificationBundle\Dispatching;

/**
 * @internal
 *
 * @author Sergei Lissovski <sergei.lissovski@modera.org>
 */
class DuplicateChannelsFilterer
{
    /**
     * @param ChannelInterface[] $channels
     * @param array $ids
     *
     * @return array
     */
    public static function filter(array $channels, array $ids = [])
    {
        $channelsMap = array();
        $channelsHashToIdsAndAliasesMap = array();
        foreach ($channels as $channel) {
            $hash = spl_object_hash($channel);

            $channelsMap[$hash] = $channel;
            $channelsHashToIdsAndAliasesMap[$hash] = array_merge([$channel->getId()], $channel->getAliases());
        }

        $result = [];
        foreach ($ids as $id) {
            foreach ($channelsHashToIdsAndAliasesMap as $hash=>$channelIdAndAliases) {
                if (in_array($id, $channelIdAndAliases)) {
                    $result[] = $channelsMap[$hash];
                    unset($channelsMap[$hash]);
                    unset($channelsHashToIdsAndAliasesMap[$hash]);
                }
            }
        }

        return $result;
    }
}