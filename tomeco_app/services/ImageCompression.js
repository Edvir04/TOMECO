import * as ImageManipulator from 'expo-image-manipulator';
import * as FileSystem from 'expo-file-system/legacy';

/**
 * Image Compression Service
 * Compresses images to reduce file size for uploads
 */

// Maximum dimensions for compressed images
const MAX_WIDTH = 1920;
const MAX_HEIGHT = 1920;
const COMPRESSION_QUALITY = 0.7; // 70% quality (good balance between size and quality)

/**
 * Compress an image file
 * @param {string} imageUri - URI of the image to compress
 * @param {Object} options - Compression options
 * @returns {Promise<{uri: string, width: number, height: number, size?: number}>}
 */
export const compressImage = async (imageUri, options = {}) => {
  try {
    const {
      maxWidth = MAX_WIDTH,
      maxHeight = MAX_HEIGHT,
      quality = COMPRESSION_QUALITY,
    } = options;

    console.log('ImageCompression - Compressing image:', imageUri.substring(0, 50) + '...');

    // Compress and resize the image
    // ImageManipulator will automatically maintain aspect ratio
    const compressedImage = await ImageManipulator.manipulateAsync(
      imageUri,
      [
        { resize: { width: maxWidth, height: maxHeight } },
      ],
      {
        compress: quality,
        format: ImageManipulator.SaveFormat.JPEG,
      }
    );

    // Get file size
    const fileInfo = await FileSystem.getInfoAsync(compressedImage.uri);
    const fileSize = fileInfo.exists && fileInfo.size ? fileInfo.size : null;

    console.log('ImageCompression - Compression complete:', {
      compressed: { 
        width: compressedImage.width, 
        height: compressedImage.height,
        size: fileSize ? `${(fileSize / 1024 / 1024).toFixed(2)} MB` : 'unknown',
      },
    });

    return {
      uri: compressedImage.uri,
      width: compressedImage.width,
      height: compressedImage.height,
      size: fileSize,
    };
  } catch (error) {
    console.error('ImageCompression - Error compressing image:', error);
    // Return original URI if compression fails
    return {
      uri: imageUri,
      width: 0,
      height: 0,
    };
  }
};

/**
 * Compress multiple images
 * @param {Array<{uri: string, type?: string, name?: string}>} images - Array of image objects
 * @param {Object} options - Compression options
 * @returns {Promise<Array<{uri: string, type: string, name: string, width: number, height: number}>>}
 */
export const compressImages = async (images, options = {}) => {
  const compressedImages = [];
  
  for (let i = 0; i < images.length; i++) {
    const image = images[i];
    try {
      const compressed = await compressImage(image.uri, options);
      compressedImages.push({
        uri: compressed.uri,
        type: image.type || 'image/jpeg',
        name: image.name || `compressed_image_${i}.jpg`,
        width: compressed.width,
        height: compressed.height,
        size: compressed.size,
      });
    } catch (error) {
      console.error(`ImageCompression - Error compressing image ${i}:`, error);
      // Include original image if compression fails
      compressedImages.push(image);
    }
  }
  
  return compressedImages;
};

/**
 * Compress a base64 signature image
 * IMPORTANT: Uses PNG format to preserve white background and avoid black transparency issues
 * @param {string} base64Signature - Base64 encoded signature
 * @param {Object} options - Compression options
 * @returns {Promise<string>} Compressed base64 signature
 */
export const compressBase64Signature = async (base64Signature, options = {}) => {
  try {
    if (!base64Signature) return base64Signature;

    // Extract base64 data (remove data URI prefix if present)
    let base64Data = base64Signature;
    if (base64Signature.includes(',')) {
      base64Data = base64Signature.split(',')[1];
    }

    // Save to temporary file
    const tempUri = `${FileSystem.cacheDirectory}temp_signature_${Date.now()}.png`;
    await FileSystem.writeAsStringAsync(tempUri, base64Data, {
      encoding: FileSystem.EncodingType.Base64,
    });

    // Compress the image
    const {
      maxWidth = 800, // Signatures can be smaller
      maxHeight = 400,
      quality = 0.8, // Higher quality for signatures
    } = options;

    // IMPORTANT: Use PNG format for signatures to preserve white background
    // JPEG conversion causes transparent areas to become black
    const compressed = await ImageManipulator.manipulateAsync(
      tempUri,
      [
        { resize: { width: maxWidth, height: maxHeight } },
      ],
      {
        compress: quality,
        format: ImageManipulator.SaveFormat.PNG, // Use PNG instead of JPEG for signatures
      }
    );

    // Read compressed image as base64
    const compressedBase64 = await FileSystem.readAsStringAsync(compressed.uri, {
      encoding: FileSystem.EncodingType.Base64,
    });

    // Clean up temporary file
    try {
      await FileSystem.deleteAsync(tempUri, { idempotent: true });
      await FileSystem.deleteAsync(compressed.uri, { idempotent: true });
    } catch (cleanupError) {
      console.warn('ImageCompression - Error cleaning up temp files:', cleanupError);
    }

    // Return with data URI prefix (PNG format)
    return `data:image/png;base64,${compressedBase64}`;
  } catch (error) {
    console.error('ImageCompression - Error compressing signature:', error);
    // Return original if compression fails
    return base64Signature;
  }
};

export default {
  compressImage,
  compressImages,
  compressBase64Signature,
};

