// cards/TicketIssuedOwnCard.js
import React from 'react';
import { View, Text, StyleSheet } from 'react-native';

/**
 * Single ticket card view.
 * Expects a "request" prop; defensively handles undefined.
 */
const TicketIssuedOwnCard = ({ request }) => {
  const r = request ?? {}; // <-- prevents "of undefined" crashes

  const formatDate = (dateString, includeTime = false) => {
    if (!dateString) return '—';
    const date = new Date(dateString);
    const options = includeTime
      ? { year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: 'numeric', hour12: true }
      : { year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
  };

  const violations = [
    r.violation1, r.violation2, r.violation3, r.violation4, r.violation5, r.violation6,
    r.violation7, r.violation8, r.violation9, r.violation10, r.violation11, r.violation12,
  ].filter(v => v && v !== 'true' && v !== 'false').join('/');

  return (
    <View style={styles.card}>
      <Text style={styles.label}>Driver's Name: {r.drivers_name ?? '—'}</Text>
      <Text style={styles.label}>Address: {r.address ?? '—'}</Text>
      <Text style={styles.label}>Date: {formatDate(r.required_date)}</Text>

      <View style={styles.line} />

      <Text style={styles.cardText}>
        D/L Permit #: {r.drivers_permit ?? '—'} /
        {r.prof && r.prof !== 'true' && r.prof !== 'false' ? <Text> {r.prof}</Text> : null}
        {r.np && r.np !== 'true' && r.np !== 'false' ? <Text> / {r.np}</Text> : null}
        {r.sp && r.sp !== 'true' && r.sp !== 'false' ? <Text> / {r.sp}</Text> : null}
      </Text>

      {violations ? (
        <Text style={styles.cardText}>Violation: {violations}</Text>
      ) : null}

      <Text style={styles.cardText}>
        Driver Agreement:
        {r.admitted && r.admitted !== 'true' && r.admitted !== 'false' ? <Text> {r.admitted}</Text> : null}
        {r.under_protest && r.under_protest !== 'true' && r.under_protest !== 'false' ? <Text> / {r.under_protest}</Text> : null}
      </Text>

      <View style={styles.line} />
      <Text style={styles.requestedAtText}>Requested at: {formatDate(r.created_at, true)}</Text>
      <Text style={styles.requestedAtText}>Ticket ID: 2025{r.id ?? '—'}</Text>
    </View>
  );
};

const styles = StyleSheet.create({
  card: {
  backgroundColor: '#fafafa',  
  borderWidth: 1,
  borderColor: 'lightgray', // softer than pure white (#fff)
  margin: 10,
  padding: 15,
  borderRadius: 10,
  marginBottom: 10,

  // shadow (iOS)
  shadowColor: '#000',
  shadowOpacity: 0.08,          // very subtle shadow
  shadowRadius: 4,
  shadowOffset: { width: 0, height: 2 },

  // shadow (Android)
  elevation: 3,
},

  label: {
    fontSize: 16,
    fontWeight: 'bold',
    marginRight: 5,
    color: 'darkred',
    marginBottom: 4,
  },
  line: {
    borderBottomColor: 'darkred',
    borderBottomWidth: 1,
    marginVertical: 10,
  },
  cardText: {
    fontSize: 16,
    marginBottom: 5,
    color: 'darkred',
  },
  requestedAtText: {
    color: 'darkred',
  },
  button: {
    backgroundColor: 'lightblue',
    paddingVertical: 10,
    paddingHorizontal: 20,
    borderRadius: 5,
    marginTop: 10,
    alignSelf: 'center',
  },
  buttonText: {
    color: 'white',
    fontSize: 16,
  },
});

export default TicketIssuedOwnCard;
