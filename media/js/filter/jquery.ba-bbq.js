// jQuery Param Alternative
const phUrlParam = (data) => {
    if (data == null) {
        return "";
    }

    const urlParams = [];
    const rbracket = /\[\]$/;

    const add = (name, valueOrFunction) => {
        let value = typeof valueOrFunction === "function" ? valueOrFunction() : valueOrFunction;
        if (value == null) {
            value = "";
        }

        urlParams.push(`${encodeURIComponent(name)}=${encodeURIComponent(value)}`);
    };

    const buildParams = (prefix, obj) => {
        if (Array.isArray(obj)) {
            obj.forEach((value, index) => {
                if (rbracket.test(prefix)) {
                    add(prefix, value);
                } else {
                    const i = typeof value === "object" && value != null ? index : "";
                    buildParams(`${prefix}[${i}]`, value);
                }
            });
        } else if (typeof obj === "object" && obj != null) {
            for (const [name, value] of Object.entries(obj)) {
                buildParams(`${prefix}[${name}]`, value);
            }
        } else {
            add(prefix, obj);
        }
    };

    if (Array.isArray(data)) {
        // If an array was passed in,
        // assume that it is a collection of form elements:
        data.forEach((el) => add(el.name, el.value));
    } else {
        for (const [name, value] of Object.entries(data)) {
            buildParams(name, value);
        }
    }

    return urlParams.join("&");
};

function phIsString(arg) {
    return typeof arg === "string";
}

// Get location.search (or what you'd expect location.search to be) sans any
// leading #. Thanks for making this necessary, IE6!
function phGetQueryString(url) {
    return url.replace(/(?:^[^?#]*\?([^#]*).*$)?.*/, "$1");
}

function phUrlParamQueryString(url, params, merge_mode) {
    var result, qs, matches, url_params, hash;

    if (params !== undefined) {
        // Build URL by merging params into url string.

        // matches[1] = url part that precedes params, not including trailing ?/#
        // matches[2] = params, not including leading ?/#
        // matches[3] = if in 'querystring' mode, hash including leading #, otherwise ''
        matches = url.match(/^([^#?]*)\??([^#]*)(#?.*)/);

        // Get the hash if in 'querystring' mode, and it exists.
        hash = matches[3] || "";

        if (merge_mode === 2 && phIsString(params)) {
            // If merge_mode is 2 and params is a string, merge the fragment / query
            // string into the URL wholesale, without converting it into an object.
            qs = params.replace(/^.*\?|#.*$/g, "");
        } else {
            // Convert relevant params in url to object.
            url_params = phUrlDeParam(matches[2]);

            params = phIsString(params)
                ? // Convert passed params string into object.
                  /*jq_deparam[ is_fragment ? str_fragment : str_querystring ]( params )*/ phUrlDeParamQueryString(params)
                : // Passed params object.
                  params;

            //console.log("=>");
            //console.log(params)  ;
            //console.log(url_params)  ;

            //  console.log($.extend( {}, params, url_params ));
            //   console.log("-");
            //  console.log({...params, ...url_params });
            //  console.log("<=");
            /*  qs = merge_mode === 2 ? params                              // passed params replace url params
        : merge_mode === 1  ? $.extend( {}, params, url_params )  // url params override passed params
        : $.extend( {}, url_params, params );                     // passed params override url params
      */

            qs =
                merge_mode === 2
                    ? params // passed params replace url params
                    : merge_mode === 1
                    ? { ...params, ...url_params } // url params override passed params
                    : { ...url_params, ...params }; // passed params override url params

            // Convert params object to a string.

            /// qs2 = qs;
            //qs = jq_param( qs );
            qs = phUrlParam(qs);
            ///  console.log(qs);
            ///  qs2 = phocaUrlParam(qs2);
            ///  console.log(qs2);

            // Unescape characters specified via $.param.noEscape. Since only hash-
            // history users have requested this feature, it's only enabled for
            // fragment-related params strings.
            /*if ( is_fragment ) {
        qs = qs.replace( re_no_escape, decode );
      }*/
        }

        // Build URL from the base url, querystring and hash. In 'querystring'
        // mode, ? is only added if a query string exists. In 'fragment' mode, #
        // is always added.
        ///result = matches[1] + ( is_fragment ? '#' : qs || !matches[1] ? '?' : '' ) + qs + hash;
        result = matches[1] + (qs || !matches[1] ? "?" : "") + qs + hash;
    } else {
        // If URL was passed in, parse params from URL string, otherwise parse
        // params from window.location.
        result = phGetQueryString(url !== undefined ? url : window.location.href);
    }

    return result;
}
/*jq_param[ str_querystring ]                  = curry( jq_param_sub, 0, get_querystring );
  jq_param[ str_fragment ] = jq_param_fragment = curry( jq_param_sub, 1, get_fragment );*/

const phUrlDeParam = function (params, coerce) {
    var obj = {},
        coerce_types = { true: !0, false: !1, null: null };

    // Iterate over all name=value pairs.

    var paramsA = params.replace(/\+/g, " ").split("&");

    /*console.log("Params:");
  console.log(params);
  console.log("ParamsA:");
  console.log(paramsA);*/

    paramsA.forEach(function (v) {
        //$.each( params.replace( /\+/g, ' ' ).split( '&' ), function(j,v){

        // console.log(v);
        //console.log(params.replace( /\+/g, ' ' ).split( '&' ));
        var param = v.split("="),
            key = decodeURIComponent(param[0]),
            val,
            cur = obj,
            i = 0,
            // If key is more complex than 'foo', like 'a[]' or 'a[b][c]', split it
            // into its component parts.
            keys = key.split("]["),
            keys_last = keys.length - 1;

        // If the first keys part contains [ and the last ends with ], then []
        // are correctly balanced.
        if (/\[/.test(keys[0]) && /\]$/.test(keys[keys_last])) {
            // Remove the trailing ] from the last keys part.
            keys[keys_last] = keys[keys_last].replace(/\]$/, "");

            // Split first keys part into two parts on the [ and add them back onto
            // the beginning of the keys array.
            keys = keys.shift().split("[").concat(keys);

            keys_last = keys.length - 1;
        } else {
            // Basic 'foo' style key.
            keys_last = 0;
        }

        // Are we dealing with a name=value pair, or just a name?
        if (param.length === 2) {
            val = decodeURIComponent(param[1]);

            // Coerce values.
            if (coerce) {
                val =
                    val && !isNaN(val)
                        ? +val // number
                        : val === "undefined"
                        ? undefined // undefined
                        : coerce_types[val] !== undefined
                        ? coerce_types[val] // true, false, null
                        : val; // string
            }

            if (keys_last) {
                // Complex key, build deep object structure based on a few rules:
                // * The 'cur' pointer starts at the object top-level.
                // * [] = array push (n is set to array length), [n] = array if n is
                //   numeric, otherwise object.
                // * If at the last keys part, set the value.
                // * For each keys part, if the current level is undefined create an
                //   object or array based on the type of the next keys part.
                // * Move the 'cur' pointer to the next level.
                // * Rinse & repeat.
                for (; i <= keys_last; i++) {
                    key = keys[i] === "" ? cur.length : keys[i];
                    cur = cur[key] = i < keys_last ? cur[key] || (keys[i + 1] && isNaN(keys[i + 1]) ? {} : []) : val;
                }
            } else {
                // Simple key, even simpler rules, since only scalars and shallow
                // arrays are allowed.

                if (Array.isArray(obj[key])) {
                    // val is already an array, so push on the next value.
                    obj[key].push(val);
                } else if (obj[key] !== undefined) {
                    // val isn't an array, but since a second value has been specified,
                    // convert val into an array.
                    obj[key] = [obj[key], val];
                } else {
                    // val is a scalar.
                    obj[key] = val;
                }
            }
        } else if (key) {
            // No value was defined, so set something meaningful.
            obj[key] = coerce ? undefined : "";
        }
    });

    return obj;
};

function phUrlDeParamQueryString(url_or_params, coerce) {
    if (url_or_params === undefined || typeof url_or_params === "boolean") {
        // url_or_params not specified.
        coerce = url_or_params;
        ///url_or_params = jq_param[ str_querystring ]();
        url_or_params = phUrlParamQueryString();
    } else {
        url_or_params = phIsString(url_or_params) ? url_or_params.replace(/^.*\?|#.*$/g, "") : url_or_params;
    }

    return phUrlDeParam(url_or_params, coerce);
}

/*
jq_deparam[ str_querystring ]                    = curry( jq_deparam_sub, 0 );
jq_deparam[ str_fragment ] = jq_deparam_fragment = curry( jq_deparam_sub, 1 );*/
