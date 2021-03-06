'use strict';

angular.module('raspiSurveillance.controllers').controller('LivestreamController', [
  '$scope', '$rootScope', function ($scope, $rootScope) {

    // TODO: Revert back to native video player, or refactor and clean up

    // Attributes
    $scope.streamUrl = null;

    // Actions
    $scope.initialize = function () {
      $scope.videoPlayerContainer = $('#video-player-container');

      $scope.resizeVideoPlayer();
    }

    $scope.resizeVideoPlayer = function () {
      if ($("#video-player")) {
        var width = $("[ng-controller=LivestreamController]").width();
        var height = width * 9 / 16; // 16:9 relation

        var videoPlayer = $('#video-player embed');
        videoPlayer.width(width);
        videoPlayer.height(height);
      }
    }


    $scope.playStream = function (url, type) {
      console.info('Playing livestream "' + url + '" (' + type + ')');
      $scope.streamUrl = url;

      $rootScope.$broadcast('playingStream', url, type);

      // Add /replace video player
      var videoPlayer = '<object classid="clsid:9BE31822-FDAD-461B-AD51-BE1D1C159921" codebase="http://download.videolan.org/pub/videolan/vlc/last/win32/axvlc.cab" id="video-player">';
      videoPlayer += '    <param name="Src" value="' + url + '" />';
      videoPlayer += '    <embed type="application/x-vlc-plugin" pluginspage="http://www.videolan.org" name="vlc"';
      videoPlayer += '      width="640" height="480" target="' + url + '" />';
      videoPlayer += '</object>';
      $scope.videoPlayerContainer.html(videoPlayer);

      $scope.resizeVideoPlayer();
    }

    $scope.$on('playStream', function (event, url, type) {
      $scope.playStream(url, type);
    });

    $scope.playStreamFromUrl = function () {
      $scope.playStream($scope.customUrl, 'video/mp4');
    }

    $scope.$on('removingStreamSource', function (event, url) {
      if ($scope.streamUrl && url.toLowerCase() === $scope.streamUrl.toLowerCase()) {
        $scope.streamUrl = null;
        console.info('Stopping livestream (stream source "' + url + '" is being removed)');

        // Remove video player
        $scope.videoPlayerContainer.html('');
      }
    });

    $scope.initialize();

  }
]);
