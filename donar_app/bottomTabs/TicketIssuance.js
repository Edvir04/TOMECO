import React, { useState } from 'react';
import {
  View,
  Text,
  ScrollView,
  ImageBackground,
  TouchableOpacity,
  TextInput,
  Alert,
  Platform,
  StyleSheet,
  Image,
} from 'react-native';
import { FAB, Checkbox  } from 'react-native-paper';
import DateTimePicker from '@react-native-community/datetimepicker';
import ViewModal from '../modals/View';
import * as ImagePicker from 'expo-image-picker';
import Constants from 'expo-constants';


const TicketIssuance = ({ route,navigation }) => {

  const getBaseUrl = () => {
    if (Constants.expoConfig?.hostUri) {
      const baseUrl = Constants.expoConfig.hostUri.split(':').shift();
      return `http://${baseUrl}:3000`; // Adjust to your backend port
    }
    return 'http://localhost:3000'; // Fallback if no IP detected
  };

  const { user } = route.params;
  const [viewModalVisible, setViewModalVisible] = useState(false);
  const [showDatePicker, setShowDatePicker] = useState(false);
  const [requiredDate, setRequiredDate] = useState(new Date());
  const [prof, setProf] = useState(false);
  const [Np, setNp] = useState(false);
  const [Sp, setSp] = useState(false);
  const [Violation1, setViolation1] = useState(false);
  const [Violation2, setViolation2] = useState(false);
  const [Violation3, setViolation3] = useState(false);
  const [Violation4, setViolation4] = useState(false);
  const [Violation5, setViolation5] = useState(false);
  const [Violation6, setViolation6] = useState(false);
  const [Violation7, setViolation7] = useState(false);
  const [Violation8, setViolation8] = useState(false);
  const [Violation9, setViolation9] = useState(false);
  const [Violation10, setViolation10] = useState(false);
  const [Violation11, setViolation11] = useState(false);
  const [Violation12, setViolation12] = useState(false);
  const [Admitted, setAdmitted] = useState(false);
  const [underProtest, setUnderProstest] = useState(false);
  const [formData, setFormData] = useState({
    driversName: '',
    Address: '',
    driversPermit: '',
    pltNumber: '',
    crNumber: '',
    orNumber: '',
    make: '',
    model: '',
    type: '',
    year: '',
    owner: '',
    ownerAddress: '',
    Place: '',
    Accident: '',
    apprehendingOfficer:'',
    tomecoID:'',


   
    userID: user.id,
  });

  const [imgUrl, setImgUrl] = useState(
    'https://static.vecteezy.com/system/resources/thumbnails/004/640/699/small/circle-upload-icon-button-isolated-on-white-background-vector.jpg'
  );
  const [imageBase64, setImageBase64] = useState('');

  const openImagePicker = async (sourceType) => {
    try {
      let permissionResult;
      if (sourceType === 'camera') {
        console.log("Attempting to open camera...");
        permissionResult = await ImagePicker.requestCameraPermissionsAsync();
        if (permissionResult.granted === false) {
          Alert.alert("Permission Denied", "You've refused to allow this app to access your camera!");
          return;
        }
        console.log("Permission granted. Launching camera...");
        const result = await ImagePicker.launchCameraAsync({
          allowsEditing: true,
          aspect: [4, 3],
          quality: 1,
          base64: true, // Enable base64 encoding
        });
        console.log("Camera Result:", result);
        handleImageResult(result);
      } else {
        console.log("Attempting to open image gallery...");
        permissionResult = await ImagePicker.requestMediaLibraryPermissionsAsync();
        if (permissionResult.granted === false) {
          Alert.alert("Permission Denied", "You've refused to allow this app to access your image gallery!");
          return;
        }
        console.log("Permission granted. Launching image picker...");
        const result = await ImagePicker.launchImageLibraryAsync({
          mediaTypes: ImagePicker.MediaTypeOptions.Images,
          allowsEditing: true,
          aspect: [4, 3],
          quality: 1,
          base64: true, // Enable base64 encoding
        });
        console.log("Image Gallery Result:", result);
        handleImageResult(result);
      }
    } catch (error) {
      console.error('Error when opening image picker:', error);
    }
  };
  
  const handleImageResult = (result) => {
    if (!result.canceled) {
      if (result.uri) {
        console.log("Selected Image:", result.uri);
        setImgUrl(result.uri);
        setImageBase64(result.base64); // Set base64 image string
      } else if (result.assets && result.assets.length > 0 && result.assets[0].uri) {
        console.log("Selected Image:", result.assets[0].uri);
        setImgUrl(result.assets[0].uri);
        setImageBase64(result.assets[0].base64); // Set base64 image string
      } else {
        console.log("No URI found in image result.");
      }
    } else {
      console.log("Image picker cancelled.");
    }
  };

  const openCameraLib = () => {
    Alert.alert(
      'Choose Image Source',
      'Select the source for uploading the document',
      [
        {
          text: 'Camera',
          onPress: () => openImagePicker('camera'),
        },
        {
          text: 'Gallery',
          onPress: () => openImagePicker('gallery'),
        },
      ],
      { cancelable: true }
    );
  };

  const handleDateChange = (event, selectedDate) => {
    if (event.type === 'dismissed') {
      setShowDatePicker(false);
      return;
    }
    const currentDate = selectedDate || requiredDate;
    setShowDatePicker(Platform.OS === 'ios');
    setRequiredDate(currentDate);
  };

  

  const handleSubmit = async () => {
    try {
      // Create the request body
      const requestBody = {
        ...formData,
        userID: user.id,
        requiredDate: requiredDate.toISOString(),
        image: imageBase64, // Send the base64 string directly
        prof,
        Np,
        Sp,
        Violation1,
        Violation2,
        Violation3,
        Violation4,
        Violation5,
        Violation6,
        Violation7,
        Violation8,
        Violation9,
        Violation10,
        Violation11,
        Violation12,
        Admitted,
        underProtest,
      };
  
      // Send the data to the server
      const response = await fetch(`${getBaseUrl()}/ticket_issuance`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestBody),
      });
  
      if (response.ok) {
        Alert.alert('Success', 'Your Ticket Issuance is submitted successfully', [
          { 
            text: 'OK', 
            onPress: () => {
              // Refresh the screen
              // navigation.goBack(); // Navigate back, which will refresh the previous screen
              setFormData({
                driversName: '',
                Address: '',
                driversPermit: '',
                pltNumber: '',
                crNumber: '',
                orNumber: '',
                make: '',
                model: '',
                type: '',
                year: '',
                owner: '',
                ownerAddress: '',
                Place: '',
                Accident: '',
                apprehendingOfficer:'',
                tomecoID:'',
              });
              // Reset the image to null
            setImageBase64(null);

            // Reset other form variables like prof, Np, Sp, etc.
            setProf(false); // Reset prof to initial state (empty string or its default value)
            setNp(false);   // Reset Np
            setSp(false);   // Reset Sp
            setViolation1(false); // Reset violations and other variables as needed
            setViolation2(false);
            setViolation3(false);
            setViolation4(false);
            setViolation5(false);
            setViolation6(false);
            setViolation7(false);
            setViolation8(false);
            setViolation9(false);
            setViolation10(false);
            setViolation11(false);
            setViolation12(false);
            setAdmitted(false); // Reset Admitted if it's a boolean field
            setUnderProstest(false); 
            }
          },
        ]);
      } else {
        const data = await response.json();
        Alert.alert('Submission failed', data.errorMessage || 'Unknown error');
      }
    } catch (error) {
      console.error('Error:', error);
    }
  };
  
  
  
  
  

  
  return (
    <>
      <ImageBackground
        source={require('../assets/background1.png')}
        style={styles.background}
      >
        <ScrollView contentContainerStyle={styles.scrollViewContent}>

          {/* Driver Information */}
          <View style={styles.container}>
            <TextInput
              style={styles.input}
              placeholder="Driver's Name (Last Name, First Name Middle Name)"
              value={formData.driversName}  
              onChangeText={(text) =>
                setFormData({ ...formData, driversName: text })
              }
            />
           
            <TextInput
              style={styles.input}
              placeholder="Address: Barangay 83-A (San Jose)"
              value={formData.Address}  
              onChangeText={(text) =>
                setFormData({ ...formData, Address: text })
              }
            />

            <View style={styles.separator} />

               {/* Violation Section */}
               <View style={styles.rowContainer}>
                  <TextInput
                    style={styles.input}
                    placeholder="D/L Permit #"
                    value={formData.driversPermit}  
                    onChangeText={(text) =>
                      setFormData({ ...formData, driversPermit: text })
                    }
                  />
                  <View style={styles.checkboxContainer}>
                    <Checkbox
                      status={prof ? 'checked' : 'unchecked'}
                      onPress={() => setProf(!prof)}
                      color="white"
                      uncheckedColor="white"
                    />
                    <Text style={styles.checkboxLabel}>Prof</Text>
                  </View>
                  <View style={styles.checkboxContainer}>
                    <Checkbox
                      status={Np ? 'checked' : 'unchecked'}
                      onPress={() => setNp(!Np)}
                      color="white"
                      uncheckedColor="white"
                    />
                    <Text style={styles.checkboxLabel}>N/P</Text>
                  </View>
                  <View style={styles.checkboxContainer}>
                    <Checkbox
                      status={Sp ? 'checked' : 'unchecked'}
                      onPress={() => setSp(!Sp)}
                      color="white"
                      uncheckedColor="white"
                    />
                    <Text style={styles.checkboxLabel}>S/P</Text>
                  </View>
                </View>
                <View style={styles.rowContainer}> 
                <TextInput
                    style={styles.input}
                    placeholder="PLT Number"
                    value={formData.pltNumber}  
                    onChangeText={(text) =>
                      setFormData({ ...formData, pltNumber: text })
                    }
                  />
                   <TextInput
                    style={styles.input}
                    placeholder="CR Number"
                    value={formData.crNumber}  
                    onChangeText={(text) =>
                      setFormData({ ...formData, crNumber: text })
                    }
                  />
                   <TextInput
                    style={styles.input}
                    placeholder="OR Number"
                    value={formData.orNumber}  
                    onChangeText={(text) =>
                      setFormData({ ...formData, orNumber: text })
                    }
                  />
                </View>

                
                <View style={styles.rowContainer}> 
                <TextInput
                    style={styles.input}
                    placeholder="Make"
                    value={formData.make}  
                    onChangeText={(text) =>
                      setFormData({ ...formData, make: text })
                    }
                  />
                   <TextInput
                    style={styles.input}
                    placeholder="Model"
                    value={formData.model}  
                    onChangeText={(text) =>
                      setFormData({ ...formData, model: text })
                    }
                  />
                   <TextInput
                    style={styles.input}
                    placeholder="Type"
                    value={formData.type}  
                    onChangeText={(text) =>
                      setFormData({ ...formData, type: text })
                    }
                  />
                  <TextInput
                    style={styles.input}
                    placeholder="Year"
                    value={formData.year}  
                    onChangeText={(text) =>
                      setFormData({ ...formData, year: text })
                    }
                  />
                </View>
                <TextInput
                    style={styles.input}
                    placeholder="Owner: (Last Name, First Name Middle Name)"
                    value={formData.owner}  
                    onChangeText={(text) =>
                      setFormData({ ...formData, owner: text })
                    }
                  />
                  <TextInput
              style={styles.input}
              placeholder="Owner Address: Barangay 83-A (San Jose)"
              value={formData.ownerAddress}  
              onChangeText={(text) =>
                setFormData({ ...formData, ownerAddress: text })
              }
            />

            <View style={styles.separator} />
            <Text style={{ textAlign: 'center', color: 'white', fontWeight: 'bold'}}>
              VIOLATION(S)
            </Text>
            <View style={styles.separator} />
            <Text style={{ textAlign: 'justify', color: 'white',fontSize: 12 , fontWeight: 'bold'}}>
              You are hereby cited/charged for commiting the violation (s) marked "✓" hereunder (Rule lX, CO# 2000-01) as amended and other related City Ordinance.
            </Text>
            <View style={styles.separator} />
                 
                 <View style={styles.rowContainer}>
                  <View style={styles.checkboxContainer}>
                    <Checkbox
                      status={Violation1 ? 'checked' : 'unchecked'}
                      onPress={() => setViolation1(!Violation1)}
                      color="white"
                      uncheckedColor="white"
                    />
                    <Text style={styles.checkboxLabel}>Driving Without D/L</Text>
                  </View>

                  <View style={styles.checkboxContainer}>
                    <Checkbox
                      status={Violation2 ? 'checked' : 'unchecked'}
                      onPress={() => setViolation2(!Violation2)}
                      color="white"
                      uncheckedColor="white"
                    />
                    <Text style={styles.checkboxLabel}>Unregistered Vehicle</Text>
                  </View>
                  <View style={styles.checkboxContainer}>
                    <Checkbox
                      status={Violation3 ? 'checked' : 'unchecked'}
                      onPress={() => setViolation3(!Violation3)}
                      color="white"
                      uncheckedColor="white"
                    />
                    <Text style={styles.checkboxLabel}>No Helmet</Text>
                  </View>
                </View>
                <View style={styles.rowContainer}>
                  <View style={styles.checkboxContainer}>
                    <Checkbox
                      status={Violation4 ? 'checked' : 'unchecked'}
                      onPress={() => setViolation4(!Violation4)}
                      color="white"
                      uncheckedColor="white"
                    />
                    <Text style={styles.checkboxLabel}>Illegal Parking</Text>
                  </View>

                  <View style={styles.checkboxContainer}>
                    <Checkbox
                      status={Violation5 ? 'checked' : 'unchecked'}
                      onPress={() => setViolation5(!Violation5)}
                      color="white"
                      uncheckedColor="white"
                    />
                    <Text style={styles.checkboxLabel}>Disregarding Traffic Sign</Text>
                  </View>
                  <View style={styles.checkboxContainer}>
                    <Checkbox
                      status={Violation6 ? 'checked' : 'unchecked'}
                      onPress={() => setViolation6(!Violation6)}
                      color="white"
                      uncheckedColor="white"
                    />
                    <Text style={styles.checkboxLabel}>truck Ban</Text>
                  </View>
                </View>
                <View style={styles.rowContainer}>
                  <View style={styles.checkboxContainer}>
                    <Checkbox
                      status={Violation7 ? 'checked' : 'unchecked'}
                      onPress={() => setViolation7(!Violation7)}
                      color="white"
                      uncheckedColor="white"
                    />
                    <Text style={styles.checkboxLabel}>Obstruction</Text>
                  </View>

                  <View style={styles.checkboxContainer}>
                    <Checkbox
                      status={Violation8 ? 'checked' : 'unchecked'}
                      onPress={() => setViolation8(!Violation8)}
                      color="white"
                      uncheckedColor="white"
                    />
                    <Text style={styles.checkboxLabel}>Defective HeadLight</Text>
                  </View>
                </View>
                <View style={styles.checkboxContainer}>
                    <Checkbox
                      status={Violation9 ? 'checked' : 'unchecked'}
                      onPress={() => setViolation9(!Violation9)}
                      color="white"
                      uncheckedColor="white"
                    />
                    <Text style={styles.checkboxLabel}>Operating Along National Highway</Text>
                  </View>
                  <View style={styles.checkboxContainer}>
                    <Checkbox
                      status={Violation10 ? 'checked' : 'unchecked'}
                      onPress={() => setViolation10(!Violation10)}
                      color="white"
                      uncheckedColor="white"
                    />
                    <Text style={styles.checkboxLabel}>Violation to CO # 2007-10-31 "The Anti-Littering Ordinance."</Text>
                  </View>
                  <View style={styles.checkboxContainer}>
                    <Checkbox
                      status={Violation11 ? 'checked' : 'unchecked'}
                      onPress={() => setViolation11(!Violation11)}
                      color="white"
                      uncheckedColor="white"
                    />
                    <Text style={styles.checkboxLabel}>Violation to CO # 2009-10-160 "The Anti-Smoking Ordinance."</Text>
                  </View>
                  <View style={styles.checkboxContainer}>
                    <Checkbox
                      status={Violation12 ? 'checked' : 'unchecked'}
                      onPress={() => setViolation12(!Violation12)}
                      color="white"
                      uncheckedColor="white"
                    />
                    <Text style={styles.checkboxLabel}>Violation to CO # 2007-10-66 "The Anti-urinating and Defecting Ordinance."</Text>
                  </View>

                  <View style={styles.separator} />
                  <View style={styles.rowContainer}>
                  <TextInput
                    style={styles.input}
                    placeholder="Place: Burgos Street"
                    value={formData.Place}  
                    onChangeText={(text) =>
                      setFormData({ ...formData, Place: text })
                    }
                  />
                    <TouchableOpacity
                      style={styles.input}
                      onPress={() => setShowDatePicker(true)}
                    >
                      <Text>
                        {requiredDate.toLocaleDateString([], {
                          year: 'numeric',
                          month: 'long',
                          day: 'numeric',
                        })}
                      </Text>
                    </TouchableOpacity>
                    {showDatePicker && (
                      <DateTimePicker
                        testID="dateTimePicker"
                        value={requiredDate}
                        mode="date"
                        display="default"
                        minimumDate={new Date()}
                        onChange={handleDateChange}
                      />
                    )}
                     <TextInput
                    style={styles.input}
                    placeholder="Accident: Yes/No"
                    value={formData.Accident}  
                    onChangeText={(text) =>
                      setFormData({ ...formData, Accident: text })
                    }
                  />
                  </View>
                  <Text style={{ textAlign: 'justify', color: 'white',fontSize: 12 , fontWeight: 'bold'}}>
                    Hereby, the driver is ORDERED to appear at TOMECO/City Fiscal's Office/or to the Mubicipal Trial Court in Cities within 72 hours (3 days).
                    </Text>
            
            <View style={styles.separator} />
            <Text style={{ textAlign: 'center', color: 'white', fontWeight: 'bold'}}>
              THIS SERVE AS TRAFFIC VIOLATION RECIEPT/CITATION TICKET.
            </Text>
            <View style={styles.separator} />
            <Text style={{ textAlign: 'justify', color: 'white',fontSize: 12 , fontWeight: 'bold'}}>
                   I HEREBBY PROMISE to appear at TOMECO/City Fiscal,s Office/Municipal Trial Court
                   in Cities within 72 hours (3 days) to answer the above hereincharges(s). That failure on my part is a waiver to any preliminary investigation, 
                   if any, and to whatever criminal action that may be taken against me.
                    </Text>
                    <View style={styles.rowContainer}>
                  <View style={styles.checkboxContainer}>
                    <Checkbox
                      status={Admitted ? 'checked' : 'unchecked'}
                      onPress={() => setAdmitted(!Admitted)}
                      color="white"
                      uncheckedColor="white"
                    />
                    <Text style={styles.checkboxLabel}>ADMITTED</Text>
                  </View>

                  <View style={styles.checkboxContainer}>
                    <Checkbox
                      status={underProtest ? 'checked' : 'unchecked'}
                      onPress={() => setUnderProstest(!underProtest)}
                      color="white"
                      uncheckedColor="white"
                    />
                    <Text style={styles.checkboxLabel}>UNDER PROTEST</Text>
                  </View>
                </View>
                    <View style={styles.separator} />
                    <Text style={{ textAlign: 'center', color: 'white', fontWeight: 'bold'}}>
              APPREHENSION REPORT
            </Text>
            <View style={styles.separator} />
            <Text style={{ textAlign: 'justify', color: 'white',fontSize: 12 , fontWeight: 'bold'}}>
                   Apprehending TOMECOlaw enforcer/Deputized Agent are required to Submit apprehension report to TOMECO within 24 hours, Otherwise deputation order shall be revoked
                    </Text>
                    <View style={styles.separator} />
                    <View style={styles.rowContainer}>
                  <TextInput
                    style={styles.input}
                    placeholder="Apprehending Officer: "
                    value={formData.apprehendingOfficer}  
                    onChangeText={(text) =>
                      setFormData({ ...formData, apprehendingOfficer: text })
                    }
                  />
                  
                     <TextInput
                    style={styles.input}
                    placeholder="TOMECO D/ID No."
                    value={formData.tomecoID}  
                    onChangeText={(text) =>
                      setFormData({ ...formData, tomecoID: text })
                    }
                  />
                  </View>
                    <View style={styles.separator} />
         
            <Image
              resizeMode="contain"
              style={{ width: '100%', height: 200, alignSelf: 'center' }}
              source={{ uri: imgUrl }}
            />

            <TouchableOpacity style={styles.submitButton} onPress={openCameraLib}>
              <Text style={styles.buttonText}>
                Upload Document (Camera/Gallery)
              </Text>
            </TouchableOpacity>
            <TouchableOpacity style={styles.submitButton} onPress={handleSubmit}>
              <Text style={styles.buttonText}>Submit</Text>
            </TouchableOpacity>
          </View>
        </ScrollView>
        <View style={styles.fabContainer}>
          <Text style={styles.fabText}>View my Ticket Issued</Text>
          <FAB
            style={styles.smallFab}
            icon="eye"
            color="white"
            onPress={() => setViewModalVisible(true)}
          />
        </View>
        <ViewModal
          visible={viewModalVisible}
          closeModal={() => setViewModalVisible(false)}
          user={user}
        />
      </ImageBackground>
    </>
  );
};

const styles = StyleSheet.create({
  rowContainer: {
    flexDirection: 'row', // Align all children horizontally
  },

  checkboxContainer: {
    flexDirection: 'row', // Align checkbox and label horizontally
    alignItems: 'center',
    marginRight: 3, // Space between each checkbox group
    flexShrink: 0, // Prevent the checkboxes from shrinking
  },
  checkboxLabel: {
    color: 'white',
    fontWeight: 'bold',
    marginLeft: 5, // Space between the checkbox and the label
  },
  separator: {
    height: 2,
    backgroundColor: '#ccc', // Light gray color
    marginVertical: 10,
  },
  bloodTypeButton: {
    backgroundColor: 'darkred',
    borderRadius: 15,
    paddingVertical: 10,
    paddingHorizontal: 8,
    marginHorizontal: 2,
  },
  selectedBloodTypeButton: {
    backgroundColor: 'red',
  },
  dropdown: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'center',
    marginBottom: 10,
  },
  requiredDateText: {
    color: 'red',
    fontWeight: 'bold',
    marginBottom: 5,
  },
  input: {
    flex: 1,
    marginRight: 10, // Space between TextInput and checkboxes
    backgroundColor: 'white',
    borderRadius: 20,
    padding: 10,
    marginBottom: 10,
  },
  textArea: {
    height: 100,
    textAlignVertical: 'top',
  },
  submitButton: {
    backgroundColor: 'darkred',
    borderRadius: 15,
    paddingVertical: 8,
    paddingHorizontal: 16,
    marginTop: 20,
    alignItems: 'center',
  },
  buttonText: {
    color: 'white',
  },
  container: {
    padding: 20,
  },
  scrollViewContent: {
    flexGrow: 1,
  },
  fabContainer: {
    position: 'absolute',
    bottom: 5,
    right: -4,
    flexDirection: 'row',
    alignItems: 'center',
  },
  smallFab: {
    backgroundColor: 'darkred',
    borderRadius: 50,
    marginRight: 8,
  },
  fabText: {
    fontSize: 14,
    color: 'darkred',
    marginRight: 3,
  },
  background: {
    flex: 1,
    resizeMode: 'cover',
  },
  userInfo: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 10,
  },
});

export default TicketIssuance;
